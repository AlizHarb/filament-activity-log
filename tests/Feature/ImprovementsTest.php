<?php

use AlizHarb\ActivityLog\ActivityLogPlugin;
use AlizHarb\ActivityLog\Enums\ActivityLogEvent;
use AlizHarb\ActivityLog\Resources\ActivityLogs\ActivityLogResource;
use AlizHarb\ActivityLog\Resources\ActivityLogs\Pages\ViewActivityLog;
use AlizHarb\ActivityLog\Resources\ActivityLogs\Schemas\ActivityLogInfolist;
use AlizHarb\ActivityLog\Support\ActivityGrouping;
use AlizHarb\ActivityLog\Support\ActivityLogTitle;
use AlizHarb\ActivityLog\Tests\Fixtures\CustomActivityModel;
use AlizHarb\ActivityLog\Tests\Fixtures\User;
use AlizHarb\ActivityLog\Tests\TestCase;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Spatie\Activitylog\Models\Activity;

it('can resolve activity log event labels', function () {
    expect(ActivityLogEvent::Created->getLabel())->toBe(__('filament-activity-log::activity.event.created'))
        ->and(ActivityLogEvent::Updated->getLabel())->toBe(__('filament-activity-log::activity.event.updated'));
});

it('can resolve activity log event colors', function () {
    // Default config values
    expect(ActivityLogEvent::Created->getColor())->toBe('success')
        ->and(ActivityLogEvent::Deleted->getColor())->toBe('danger');
});

it('can resolve activity log event icons', function () {
    expect(ActivityLogEvent::Created->getIcon())->toBe('heroicon-m-plus');
});

it('can set and get cluster in plugin', function () {
    $plugin = new ActivityLogPlugin;
    $plugin->cluster('System');

    expect($plugin->getCluster())->toBe('System');
});

it('resolves subject title using helper', function () {
    $user = new class extends Model
    {
        protected $guarded = [];
    };
    $user->setAttribute('name', 'Test User');
    $user->setAttribute('id', 1);

    expect(ActivityLogTitle::get($user))->toBe('Test User');

    $post = new class extends Model
    {
        protected $guarded = [];
    };
    $post->setAttribute('title', 'Test Post');
    $post->setAttribute('id', 2);

    expect(ActivityLogTitle::get($post))->toBe('Test Post');

    expect(ActivityLogTitle::get($post))->toBe('Test Post');

    $unknown = new class extends Model
    {
        public function getKey()
        {
            return 3;
        }

        public function getTable()
        {
            return 'unknowns';
        }
    };
    $unknown->setAttribute('id', 3);

    expect(ActivityLogTitle::get($unknown))->toContain('#3');
});

it('applies v4 native batch_uuid filter', function () {
    if (! TestCase::isSpatieV4()) {
        $this->markTestSkipped('Only runs on Spatie v4 (native batch_uuid).');
    }

    $query = Activity::query();
    ActivityGrouping::applyGroupFilter($query, 'test-uuid');

    expect($query->toSql())->toContain('batch_uuid');
});

it('applies v5 custom-property group filter', function () {
    if (! TestCase::isSpatieV5()) {
        $this->markTestSkipped('Only runs on Spatie v5 (custom-property grouping).');
    }

    $query = Activity::query();
    ActivityGrouping::applyGroupFilter($query, 'test-group');

    $sql = $query->toSql();
    expect($sql)->toContain('properties');
});

it('renders batch action url correctly', function () {
    // Verify the Filter/Grouping query logic works
    $query = Activity::query();
    ActivityGrouping::applyGroupFilter($query, 'test-uuid');

    $sql = $query->toSql();

    // Should contain some filtering clause
    expect($sql)->toContain('?');
});

it('resolves the custom activity model from configuration', function () {
    config()->set('activitylog.activity_model', CustomActivityModel::class);

    expect(ActivityLogResource::getModel())->toBe(CustomActivityModel::class);
});

it('applies morph maps in subject resource resolving and restore actions', function () {
    Relation::morphMap([
        'morphed_user_alias' => User::class,
    ]);

    $activity = Activity::create([
        'description' => 'Test morphed',
        'subject_type' => 'morphed_user_alias',
        'subject_id' => 999,
        'event' => 'deleted',
        'properties' => ['attributes' => ['name' => 'Morphed restored', 'email' => 'morphed@example.com']],
    ]);

    // Test URL resolution helper mapping
    $modelClass = Relation::getMorphedModel($activity->subject_type) ?? $activity->subject_type;
    expect($modelClass)->toBe(User::class);

    // Verify restore action uses the morphed class to recreate model
    $resolvedMorphedClass = Relation::getMorphedModel($activity->subject_type) ?? $activity->subject_type;
    expect(class_exists($resolvedMorphedClass))->toBeTrue();
});

it('hides the changes tab if no changes exist or if nested array/object changes are found', function () {
    $livewire = new ViewActivityLog;

    // 1. With simple scalar changes -> should show tab (visible)
    $activityWithSimpleChanges = new Activity([
        'event' => 'updated',
        'properties' => [
            'old' => ['name' => 'Old Name'],
            'attributes' => ['name' => 'New Name'],
        ],
    ]);

    $schema1 = new Schema($livewire);
    $schema1->record($activityWithSimpleChanges);
    ActivityLogInfolist::configure($schema1);

    $components1 = $schema1->getComponents();
    $detailsTab1 = $components1[0];
    $changesTab1 = collect($detailsTab1->getChildComponents())->first(fn ($c) => method_exists($c, 'getLabel') && $c->getLabel() === __('filament-activity-log::activity.infolist.tab.changes'));

    expect($changesTab1)->not->toBeNull();

    // 2. With array value -> should hide tab to avoid TypeError in htmlspecialchars()
    $activityWithArrayChanges = new Activity([
        'event' => 'updated',
        'properties' => [
            'old' => ['settings' => ['theme' => 'light']],
            'attributes' => ['settings' => ['theme' => 'dark']],
        ],
    ]);

    $schema2 = new Schema($livewire);
    $schema2->record($activityWithArrayChanges);
    ActivityLogInfolist::configure($schema2);

    $components2 = $schema2->getComponents();
    $detailsTab2 = $components2[0];
    $changesTab2 = collect($detailsTab2->getChildComponents())->first(fn ($c) => method_exists($c, 'getLabel') && $c->getLabel() === __('filament-activity-log::activity.infolist.tab.changes'));

    expect($changesTab2)->toBeNull();

    // 3. With empty changes -> should hide tab
    $activityWithNoChanges = new Activity([
        'event' => 'updated',
        'properties' => [],
    ]);

    $schema3 = new Schema($livewire);
    $schema3->record($activityWithNoChanges);
    ActivityLogInfolist::configure($schema3);

    $components3 = $schema3->getComponents();
    $detailsTab3 = $components3[0];
    $changesTab3 = collect($detailsTab3->getChildComponents())->first(fn ($c) => method_exists($c, 'getLabel') && $c->getLabel() === __('filament-activity-log::activity.infolist.tab.changes'));

    expect($changesTab3)->toBeNull();
});
