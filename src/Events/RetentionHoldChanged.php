<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Spatie\Activitylog\Models\Activity;

class RetentionHoldChanged
{
    use Dispatchable;

    public function __construct(
        public Activity $activity,
        public bool $held,
    ) {}
}
