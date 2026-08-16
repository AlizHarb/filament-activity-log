<?php

namespace AlizHarb\ActivityLog\Taps;

use AlizHarb\ActivityLog\Contracts\CollectsActivityContext;
use AlizHarb\ActivityLog\Exceptions\InvalidConfigurationException;
use AlizHarb\ActivityLog\Support\RiskProjector;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Activitylog\Contracts\Activity as ActivityContract;
use Spatie\Activitylog\Models\Activity;

class SetActivityContextTap
{
    /**
     * @param  array<string, mixed>|null  $properties
     */
    public function __invoke(ActivityContract $activity, string $eventName, ?Model $subject = null, ?Model $causer = null, ?array $properties = null): void
    {
        if (! $activity instanceof Activity) {
            throw new InvalidConfigurationException('configuration.tap_activity_model');
        }

        $context = [];

        foreach (config('filament-activity-log.auto_context.collectors', []) as $collectorClass) {
            $collector = app($collectorClass);

            if (! $collector instanceof CollectsActivityContext) {
                throw new InvalidConfigurationException('configuration.context_collector', ['class' => $collectorClass]);
            }

            $context = array_replace($context, $collector->collect(request(), $subject, $causer));
        }

        if (config('filament-activity-log.auto_context.capture_batch', true)) {
            $groupId = static::getBatchUuid();
            $context['request_id'] ??= $groupId;

            if (static::hasBatchUuidColumn($activity)) {
                // v4: use the native batch_uuid column
                $activity->setAttribute('batch_uuid', $groupId);
            } else {
                // v5: use custom-property grouping per the official docs
                $context['group'] = $groupId;
            }
        }

        $activity->properties = ($activity->properties ?? collect())->merge($context);
        app(RiskProjector::class)->project($activity);
    }

    /**
     * Get or generate a batch UUID for the current request.
     */
    protected static ?string $batchUuid = null;

    public static function getBatchUuid(): string
    {
        if (static::$batchUuid === null) {
            static::$batchUuid = request()->header('X-Request-ID')
                ?? (string) Str::uuid();
        }

        return static::$batchUuid;
    }

    /**
     * Check if the activity_log table has a native batch_uuid column (v4).
     */
    /** @var array<string, bool> */
    protected static array $hasBatchUuidColumn = [];

    protected static function hasBatchUuidColumn(Activity $activity): bool
    {
        $cacheKey = ($activity->getConnectionName() ?? 'default').':'.$activity->getTable();

        if (! array_key_exists($cacheKey, static::$hasBatchUuidColumn)) {
            try {
                static::$hasBatchUuidColumn[$cacheKey] = Schema::connection($activity->getConnectionName())
                    ->hasColumn($activity->getTable(), 'batch_uuid');
            } catch (\Throwable) {
                static::$hasBatchUuidColumn[$cacheKey] = false;
            }
        }

        return static::$hasBatchUuidColumn[$cacheKey];
    }
}
