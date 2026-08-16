<?php

declare(strict_types=1);

use AlizHarb\ActivityLog\Events\RetentionHoldChanged;
use AlizHarb\ActivityLog\Support\ActivityIntegrity;
use AlizHarb\ActivityLog\Support\RetentionManager;
use AlizHarb\ActivityLog\Tests\Fixtures\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Event;
use Spatie\Activitylog\Models\Activity;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $actor = User::create([
        'name' => 'Retention Officer',
        'email' => 'retention@example.com',
        'password' => bcrypt('password'),
    ]);

    actingAs($actor);
    config()->set('filament-activity-log.mutations.custom_authorization', fn (string $ability): bool => $ability === 'hold');
    config()->set('filament-activity-log.retention.log_activity', false);
});

it('places and releases a retention hold without invalidating integrity', function () {
    $activity = Activity::create([
        'log_name' => 'security',
        'event' => 'deleted',
        'description' => 'Protected activity',
    ]);

    Event::fake([RetentionHoldChanged::class]);

    $manager = app(RetentionManager::class);

    expect($manager->setHold($activity, true))->toBeTrue()
        ->and($activity->fresh()->retention_hold)->toBeTruthy()
        ->and(app(ActivityIntegrity::class)->verify($activity->fresh()))->toBeTrue()
        ->and($manager->setHold($activity, true))->toBeFalse()
        ->and($manager->setHold($activity, false))->toBeTrue()
        ->and($activity->fresh()->retention_hold)->toBeFalsy()
        ->and(app(ActivityIntegrity::class)->verify($activity->fresh()))->toBeTrue();

    Event::assertDispatchedTimes(RetentionHoldChanged::class, 2);
});

it('cannot place a hold outside the configured activity boundary', function () {
    $activity = Activity::create([
        'log_name' => 'tenant-b',
        'event' => 'created',
        'description' => 'Other tenant',
    ]);

    config()->set(
        'filament-activity-log.query.scope',
        fn (Builder $query): Builder => $query->where('log_name', 'tenant-a')
    );

    expect(fn () => app(RetentionManager::class)->setHold($activity, true))
        ->toThrow(RuntimeException::class, 'outside the authorized query boundary');
});

it('writes a signed compensating activity for a hold change', function () {
    config()->set('filament-activity-log.retention.log_activity', true);

    $activity = Activity::create([
        'log_name' => 'security',
        'event' => 'deleted',
        'description' => 'Evidence',
    ]);

    app(RetentionManager::class)->setHold($activity, true);

    $control = Activity::query()
        ->where('event', 'retention_hold_placed')
        ->where('subject_id', $activity->getKey())
        ->first();

    expect($control)->not->toBeNull()
        ->and($control->log_name)->toBe('audit-control')
        ->and($control->properties['retention_hold'])->toBeTrue()
        ->and(app(ActivityIntegrity::class)->verify($control))->toBeTrue();
});
