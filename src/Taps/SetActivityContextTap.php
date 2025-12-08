<?php

namespace AlizHarb\ActivityLog\Taps;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Contracts\Activity;

class SetActivityContextTap
{
    public function __invoke(Activity $activity, string $eventName, ?Model $subject = null, ?Model $causer = null, ?array $properties = null): void
    {
        /** @var \Spatie\Activitylog\Models\Activity $activity */
        $activity->properties = $activity->properties->merge([
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
