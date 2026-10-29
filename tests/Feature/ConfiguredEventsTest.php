<?php

use AlizHarb\ActivityLog\Pages\UserActivitiesPage;
use AlizHarb\ActivityLog\Resources\ActivityLogs\Pages\ListActivityLogs;
use AlizHarb\ActivityLog\Resources\ActivityLogs\Pages\ViewActivityLog;
use AlizHarb\ActivityLog\Support\ActivityEvents;
use AlizHarb\ActivityLog\Tests\Fixtures\User;
use Spatie\Activitylog\Models\Activity;

use function Pest\Livewire\livewire;

it('keeps the default events when config is untouched', function () {
    expect(ActivityEvents::options())->toBe([
        'created' => __('filament-activity-log::activity.event.created'),
        'updated' => __('filament-activity-log::activity.event.updated'),
        'deleted' => __('filament-activity-log::activity.event.deleted'),
        'restored' => __('filament-activity-log::activity.event.restored'),
    ])
        ->and(ActivityEvents::color('created'))->toBe('success')
        ->and(ActivityEvents::icon('created'))->toBe('heroicon-m-plus')
        ->and(ActivityEvents::color('deleted'))->toBe('danger')
        ->and(ActivityEvents::icon('deleted'))->toBe('heroicon-m-trash');
});

it('shows a configured event in activity log and user activity filters and columns', function () {
    $original = config('filament-activity-log.events');

    config()->set('filament-activity-log.events', array_merge(is_array($original) ? $original : [], [
        'published' => [
            'label' => 'Published',
            'icon' => 'heroicon-m-check-badge',
            'color' => 'info',
        ],
    ]));

    try {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'events@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        $published = Activity::query()->create([
            'log_name' => 'default',
            'description' => 'Article published',
            'event' => 'published',
            'causer_type' => User::class,
            'causer_id' => $user->getKey(),
            'properties' => [],
        ]);

        $created = Activity::query()->create([
            'log_name' => 'default',
            'description' => 'Article drafted',
            'event' => 'created',
            'causer_type' => User::class,
            'causer_id' => $user->getKey(),
            'properties' => [],
        ]);

        $logs = livewire(ListActivityLogs::class)->loadTable();
        $eventColumn = $logs->instance()->getTable()->getColumn('event');

        expect($logs->instance()->getTable()->getFilter('event')->getOptions())
            ->toHaveKey('published', 'Published')
            ->toHaveKey('created', __('filament-activity-log::activity.event.created'))
            ->toHaveKey('updated')
            ->toHaveKey('deleted')
            ->toHaveKey('restored')
            ->and($eventColumn->formatState('published'))->toBe('Published')
            ->and($eventColumn->getColor('published'))->toBe('info')
            ->and($eventColumn->getIcon('published'))->toBe('heroicon-m-check-badge')
            ->and($eventColumn->formatState('created'))->toBe(__('filament-activity-log::activity.event.created'))
            ->and($eventColumn->getColor('created'))->toBe('success')
            ->and($eventColumn->getIcon('created'))->toBe('heroicon-m-plus');

        $logs->assertCanSeeTableRecords([$published, $created])
            ->assertSee('Published')
            ->filterTable('event', 'published')
            ->assertCanSeeTableRecords([$published])
            ->assertCanNotSeeTableRecords([$created]);

        livewire(ViewActivityLog::class, ['record' => $published->getKey()])
            ->assertSee('Published');

        $activities = livewire(UserActivitiesPage::class);
        $userEventColumn = $activities->instance()->getTable()->getColumn('event');

        expect($activities->instance()->getTable()->getFilter('event')->getOptions())
            ->toHaveKey('published', 'Published')
            ->toHaveKey('created', __('filament-activity-log::activity.event.created'))
            ->and($userEventColumn->formatState('published'))->toBe('Published')
            ->and($userEventColumn->getColor('published'))->toBe('info')
            ->and($userEventColumn->getIcon('published'))->toBe('heroicon-m-check-badge');

        $activities->assertCanSeeTableRecords([$published])
            ->assertSee('Published')
            ->filterTable('event', 'published')
            ->assertCanSeeTableRecords([$published])
            ->assertCanNotSeeTableRecords([$created]);
    } finally {
        config()->set('filament-activity-log.events', $original);
    }
});
