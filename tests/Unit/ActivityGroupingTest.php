<?php

use AlizHarb\ActivityLog\Support\ActivityGrouping;
use AlizHarb\ActivityLog\Tests\TestCase;
use Spatie\Activitylog\Models\Activity;

it('returns native batch_uuid on v4 when available', function () {
    if (! TestCase::isSpatieV4()) {
        $this->markTestSkipped('Only runs on Spatie v4.');
    }

    $activity = new Activity;
    $activity->batch_uuid = 'native-uuid-123';
    $activity->properties = collect();

    expect(ActivityGrouping::getGroupId($activity))->toBe('native-uuid-123')
        ->and(ActivityGrouping::hasGroup($activity))->toBeTrue();
});

it('returns properties group on v5', function () {
    $activity = new Activity;
    $activity->properties = collect(['group' => 'group-abc']);

    // When batch_uuid column doesn't exist or is empty, should fall back to properties.group
    if (TestCase::isSpatieV5()) {
        expect(ActivityGrouping::getGroupId($activity))->toBe('group-abc')
            ->and(ActivityGrouping::hasGroup($activity))->toBeTrue();
    } else {
        // On v4, batch_uuid column exists but is null, should still fall back to properties.group
        expect(ActivityGrouping::getGroupId($activity))->toBe('group-abc');
    }
});

it('falls back to deprecated properties batch_uuid', function () {
    $activity = new Activity;
    $activity->properties = collect(['batch_uuid' => 'legacy-uuid-456']);

    $groupId = ActivityGrouping::getGroupId($activity);

    // Should find the legacy batch_uuid in properties as a fallback
    if (TestCase::isSpatieV4()) {
        // On v4, the native column exists but is null, so should fall back to properties
        expect($groupId)->toBe('legacy-uuid-456');
    } else {
        expect($groupId)->toBe('legacy-uuid-456');
    }
});

it('returns null when no group exists', function () {
    $activity = new Activity;
    $activity->properties = collect();

    if (TestCase::isSpatieV4()) {
        $activity->batch_uuid = null;
    }

    expect(ActivityGrouping::getGroupId($activity))->toBeNull()
        ->and(ActivityGrouping::hasGroup($activity))->toBeFalse();
});
