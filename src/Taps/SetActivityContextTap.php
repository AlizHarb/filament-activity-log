<?php

namespace AlizHarb\ActivityLog\Taps;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Activitylog\Contracts\Activity;

class SetActivityContextTap
{
    public function __invoke(Activity $activity, string $eventName, ?Model $subject = null, ?Model $causer = null, ?array $properties = null): void
    {
        /** @var \Spatie\Activitylog\Models\Activity $activity */
        $context = [];

        if (config('filament-activity-log.auto_context.capture_ip', true)) {
            $context['ip_address'] = request()->ip();
        }

        if (config('filament-activity-log.auto_context.capture_browser', true)) {
            $context['user_agent'] = request()->userAgent();
        }

        if (config('filament-activity-log.auto_context.capture_batch', true)) {
            $groupId = static::getBatchUuid();

            if (static::hasBatchUuidColumn()) {
                // v4: use the native batch_uuid column
                $activity->batch_uuid = $groupId;
            } else {
                // v5: use custom-property grouping per the official docs
                $context['group'] = $groupId;
            }
        }

        $activity->properties = $activity->properties->merge($context);
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
    protected static ?bool $hasBatchUuidColumn = null;

    protected static function hasBatchUuidColumn(): bool
    {
        if (static::$hasBatchUuidColumn === null) {
            try {
                static::$hasBatchUuidColumn = Schema::hasColumn('activity_log', 'batch_uuid');
            } catch (\Throwable) {
                static::$hasBatchUuidColumn = false;
            }
        }

        return static::$hasBatchUuidColumn;
    }
}
