<?php

use AlizHarb\ActivityLog\Support\ActivityChanges;
use Spatie\Activitylog\Models\Activity;

it('reads old values from properties (v4 legacy)', function () {
    $activity = new Activity;
    $activity->properties = collect([
        'old' => ['name' => 'Old Name'],
        'attributes' => ['name' => 'New Name'],
    ]);

    expect(ActivityChanges::getOldValues($activity))->toBe(['name' => 'Old Name']);
});

it('reads new values from properties (v4 legacy)', function () {
    $activity = new Activity;
    $activity->properties = collect([
        'old' => ['name' => 'Old Name'],
        'attributes' => ['name' => 'New Name'],
    ]);

    expect(ActivityChanges::getNewValues($activity))->toBe(['name' => 'New Name']);
});

it('prefers attribute_changes over properties when available', function () {
    $activity = new Activity;
    $activity->properties = collect([
        'old' => ['name' => 'Legacy Old'],
        'attributes' => ['name' => 'Legacy New'],
    ]);
    $activity->attribute_changes = [
        'old' => ['name' => 'V5 Old'],
        'attributes' => ['name' => 'V5 New'],
    ];

    expect(ActivityChanges::getOldValues($activity))->toBe(['name' => 'V5 Old'])
        ->and(ActivityChanges::getNewValues($activity))->toBe(['name' => 'V5 New']);
});

it('normalizes attribute_changes from JSON string', function () {
    $activity = new Activity;
    $activity->properties = collect();
    $activity->attribute_changes = json_encode([
        'old' => ['status' => 'draft'],
        'attributes' => ['status' => 'published'],
    ]);

    expect(ActivityChanges::getOldValues($activity))->toBe(['status' => 'draft'])
        ->and(ActivityChanges::getNewValues($activity))->toBe(['status' => 'published']);
});

it('normalizes attribute_changes from collection', function () {
    $activity = new Activity;
    $activity->properties = collect();
    $activity->attribute_changes = collect([
        'old' => ['color' => 'red'],
        'attributes' => ['color' => 'blue'],
    ]);

    expect(ActivityChanges::getOldValues($activity))->toBe(['color' => 'red'])
        ->and(ActivityChanges::getNewValues($activity))->toBe(['color' => 'blue']);
});

it('returns empty arrays when no changes exist', function () {
    $activity = new Activity;
    $activity->properties = collect();

    expect(ActivityChanges::getOldValues($activity))->toBe([])
        ->and(ActivityChanges::getNewValues($activity))->toBe([])
        ->and(ActivityChanges::hasChanges($activity))->toBeFalse()
        ->and(ActivityChanges::hasOldValues($activity))->toBeFalse()
        ->and(ActivityChanges::hasNewValues($activity))->toBeFalse();
});

it('detects changes correctly', function () {
    $activity = new Activity;
    $activity->properties = collect([
        'old' => ['name' => 'Old'],
        'attributes' => ['name' => 'New'],
    ]);

    expect(ActivityChanges::hasChanges($activity))->toBeTrue()
        ->and(ActivityChanges::hasOldValues($activity))->toBeTrue()
        ->and(ActivityChanges::hasNewValues($activity))->toBeTrue();
});

it('handles null attribute_changes gracefully', function () {
    $activity = new Activity;
    $activity->properties = collect(['old' => ['a' => 1]]);

    expect(ActivityChanges::getAttributeChanges($activity))->toBe([]);
});

it('handles invalid JSON attribute_changes gracefully', function () {
    $activity = new Activity;
    $activity->properties = collect();
    $activity->attribute_changes = 'not-valid-json';

    expect(ActivityChanges::getAttributeChanges($activity))->toBe([]);
});
