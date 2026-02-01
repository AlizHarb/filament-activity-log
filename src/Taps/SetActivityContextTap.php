<?php

namespace AlizHarb\ActivityLog\Taps;

use Illuminate\Database\Eloquent\Model;
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
            // Check for existing request ID or use a static one for the request lifecycle
            $context['batch_uuid'] = static::getBatchUuid();
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
                ?? (string) \Illuminate\Support\Str::uuid();
        }

        return static::$batchUuid;
    }
}
