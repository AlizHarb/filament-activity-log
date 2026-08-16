<?php

declare(strict_types=1);

use AlizHarb\ActivityLog\Support\ActivityIntegrity;
use Illuminate\Support\Facades\Artisan;
use Spatie\Activitylog\Models\Activity;

it('backfills only missing risk and integrity metadata', function () {
    $activity = Activity::create([
        'log_name' => 'security',
        'event' => 'deleted',
        'description' => 'Backfill target',
    ]);

    $activity->newQueryWithoutScopes()->whereKey($activity->getKey())->toBase()->update([
        'risk_score' => null,
        'risk_level' => null,
        'integrity_hash' => null,
    ]);

    expect(Artisan::call('filament-activity-log:backfill', ['--only-missing' => true, '--chunk' => 1]))->toBe(0);

    $activity = $activity->fresh();

    expect($activity->risk_level)->not->toBeNull()
        ->and($activity->integrity_hash)->toHaveLength(64)
        ->and(app(ActivityIntegrity::class)->verify($activity))->toBeTrue();
});

it('returns a failure exit code when integrity verification detects tampering', function () {
    $activity = Activity::create([
        'log_name' => 'default',
        'event' => 'created',
        'description' => 'Original',
    ]);

    $activity->newQueryWithoutScopes()->whereKey($activity->getKey())->toBase()->update([
        'description' => 'Tampered',
    ]);

    expect(Artisan::call('filament-activity-log:verify-integrity', ['--chunk' => 1]))->toBe(1)
        ->and(Artisan::output())->toContain((string) $activity->getKey());
});

it('reports buffered writes as unsafe for immediate metadata guarantees', function () {
    config()->set('activitylog.buffer.enabled', true);

    expect(Artisan::call('filament-activity-log:doctor'))->toBe(1)
        ->and(Artisan::output())->toContain('Immediate metadata events');
});
