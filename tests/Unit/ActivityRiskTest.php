<?php

use AlizHarb\ActivityLog\Support\ActivityRisk;
use Spatie\Activitylog\Models\Activity;

it('scores destructive and sensitive activity as high risk', function () {
    $activity = new Activity;
    $activity->event = 'deleted';
    $activity->log_name = 'security';
    $activity->properties = collect([
        'old' => ['password' => 'secret'],
    ]);

    expect(ActivityRisk::score($activity))->toBeGreaterThanOrEqual(80)
        ->and(ActivityRisk::level($activity))->toBe('critical')
        ->and(ActivityRisk::color($activity))->toBe('danger');
});

it('scores ordinary activity as low risk', function () {
    $activity = new Activity;
    $activity->event = 'created';
    $activity->log_name = 'default';
    $activity->properties = collect([
        'attributes' => ['name' => 'Demo'],
    ]);

    expect(ActivityRisk::level($activity))->toBe('low');
});
