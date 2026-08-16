<?php

declare(strict_types=1);

use AlizHarb\ActivityLog\Support\ActivityCache;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;

it('caches aggregate values and invalidates them explicitly', function () {
    $calls = 0;
    $cache = app(ActivityCache::class);

    $calculate = function () use (&$calls): int {
        return ++$calls;
    };

    expect($cache->remember('example', $calculate))->toBe(1)
        ->and($cache->remember('example', $calculate))->toBe(1);

    $cache->flush();

    expect($cache->remember('example', $calculate))->toBe(2);
});

it('invalidates aggregate values when a new activity is created', function () {
    $calls = 0;
    $cache = app(ActivityCache::class);
    $calculate = function () use (&$calls): int {
        return ++$calls;
    };

    expect($cache->remember('creation', $calculate))->toBe(1);

    Activity::create(['description' => 'Cache invalidator']);

    expect($cache->remember('creation', $calculate))->toBe(2);
});

it('disables caching for a scoped query without an explicit context key', function () {
    config()->set('filament-activity-log.query.scope', fn (Builder $query): Builder => $query);

    $calls = 0;
    $cache = app(ActivityCache::class);
    $calculate = function () use (&$calls): int {
        return ++$calls;
    };

    expect($cache->remember('scoped', $calculate))->toBe(1)
        ->and($cache->remember('scoped', $calculate))->toBe(2);
});

it('caches scoped aggregates when the application supplies a context key', function () {
    config()->set('filament-activity-log.query.scope', fn (Builder $query): Builder => $query);
    config()->set('filament-activity-log.cache.context_key', fn (): string => 'tenant-123');

    $calls = 0;
    $cache = app(ActivityCache::class);
    $calculate = function () use (&$calls): int {
        return ++$calls;
    };

    expect($cache->remember('scoped', $calculate))->toBe(1)
        ->and($cache->remember('scoped', $calculate))->toBe(1);
});
