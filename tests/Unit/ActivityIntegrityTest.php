<?php

declare(strict_types=1);

use AlizHarb\ActivityLog\Support\ActivityIntegrity;
use Spatie\Activitylog\Models\Activity;

it('persists verifiable integrity and risk metadata for new activities', function () {
    $activity = Activity::create([
        'log_name' => 'security',
        'event' => 'deleted',
        'description' => 'Sensitive deletion',
        'properties' => [
            'ip_address' => '127.0.0.1',
            'request_id' => 'request-123',
        ],
    ])->fresh();

    expect($activity->risk_score)->toBeGreaterThan(0)
        ->and($activity->risk_level)->toBeIn(['medium', 'high', 'critical'])
        ->and($activity->ip_address)->toBe('127.0.0.1')
        ->and($activity->request_id)->toBe('request-123')
        ->and($activity->integrity_hash)->toBeString()->toHaveLength(64)
        ->and(app(ActivityIntegrity::class)->verify($activity))->toBeTrue();
});

it('detects activity data tampering', function () {
    $activity = Activity::create([
        'log_name' => 'default',
        'event' => 'created',
        'description' => 'Original description',
    ])->fresh();

    $activity->newQuery()->whereKey($activity->getKey())->toBase()->update([
        'description' => 'Tampered description',
    ]);

    expect(app(ActivityIntegrity::class)->verify($activity->fresh()))->toBeFalse();
});
