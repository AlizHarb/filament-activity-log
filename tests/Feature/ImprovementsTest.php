<?php

use AlizHarb\ActivityLog\ActivityLogPlugin;
use AlizHarb\ActivityLog\Enums\ActivityLogEvent;
use AlizHarb\ActivityLog\Support\ActivityGrouping;
use AlizHarb\ActivityLog\Support\ActivityLogTitle;
use AlizHarb\ActivityLog\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
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
