<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Support;

use Closure;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ActivityCache
{
    public function __construct(protected ActivityQuery $activities) {}

    /**
     * @template TValue
     *
     * @param  Closure(): TValue  $callback
     * @return TValue
     */
    public function remember(string $key, Closure $callback): mixed
    {
        $namespace = $this->activities->cacheNamespace();
        $ttl = (int) config('filament-activity-log.cache.ttl', 60);

        if ($namespace === null || $ttl <= 0) {
            return $callback();
        }

        $store = $this->store();
        $versionKey = "{$namespace}:version";
        $version = (string) $store->get($versionKey, '1');
        $cacheKey = "{$namespace}:{$version}:".hash('sha256', $key);

        /** @var TValue $value */
        $value = $store->remember($cacheKey, $ttl, $callback);

        return $value;
    }

    public function flush(): void
    {
        $namespace = $this->activities->cacheNamespace();

        if ($namespace === null) {
            return;
        }

        $this->store()->forever("{$namespace}:version", (string) Str::uuid());
    }

    protected function store(): Repository
    {
        $store = config('filament-activity-log.cache.store');

        return Cache::store(is_string($store) && $store !== '' ? $store : null);
    }
}
