<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Events;

use AlizHarb\ActivityLog\Contracts\AuditRule;
use AlizHarb\ActivityLog\ValueObjects\AuditFinding;
use Illuminate\Foundation\Events\Dispatchable;
use Spatie\Activitylog\Models\Activity;

class AuditRuleMatched
{
    use Dispatchable;

    public function __construct(
        public Activity $activity,
        public AuditRule $rule,
        public AuditFinding $finding,
    ) {}
}
