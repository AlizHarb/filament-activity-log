<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Spatie\Activitylog\Models\Activity;

class ActivityGrouping
{
    /**
     * Get the group ID for an activity.
     *
     * Prefers native batch_uuid (v4), then properties['group'] (v5),
     * then legacy properties['batch_uuid'] as a deprecated fallback.
     */
    public static function getGroupId(Activity $activity): ?string
    {
        // v4 native batch_uuid column
        if (static::hasBatchUuidColumn() && ! empty($activity->batch_uuid)) {
            return $activity->batch_uuid;
        }

        // v5 custom-property grouping
        if (! empty($activity->properties['group'])) {
            return $activity->properties['group'];
        }

        // Deprecated fallback for older plugin-generated metadata
        if (! empty($activity->properties['batch_uuid'])) {
            return $activity->properties['batch_uuid'];
        }

        return null;
    }

    /**
     * Check if the activity belongs to a group.
     */
    public static function hasGroup(Activity $activity): bool
    {
        return static::getGroupId($activity) !== null;
    }

    /**
     * Apply a group filter to a query builder.
     *
     * On v4 (batch_uuid column exists), filters by native batch_uuid.
     * On v5, filters by properties->group.
     * Falls back to properties->batch_uuid for legacy plugin data.
     */
    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public static function applyGroupFilter(Builder $query, string $groupId): Builder
    {
        if (static::hasBatchUuidColumn()) {
            return $query->where(function (Builder $q) use ($groupId) {
                $q->where('batch_uuid', $groupId)
                    ->orWhere('properties->group', $groupId)
                    ->orWhere('properties->batch_uuid', $groupId);
            });
        }

        return $query->where(function (Builder $q) use ($groupId) {
            $q->where('properties->group', $groupId)
                ->orWhere('properties->batch_uuid', $groupId);
        });
    }

    /**
     * Check if the activity_log table has a native batch_uuid column (v4).
     */
    protected static function hasBatchUuidColumn(): bool
    {
        static $columns = [];

        $modelClass = app(ActivityQuery::class)->modelClass();
        $activity = new $modelClass;
        $connection = $activity->getConnectionName();
        $table = $activity->getTable();
        $cacheKey = ($connection ?? 'default').':'.$table;

        if (! array_key_exists($cacheKey, $columns)) {
            try {
                $columns[$cacheKey] = Schema::connection($connection)->hasColumn($table, 'batch_uuid');
            } catch (\Throwable) {
                $columns[$cacheKey] = false;
            }
        }

        return $columns[$cacheKey];
    }
}
