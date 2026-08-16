<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Contracts;

use AlizHarb\ActivityLog\ValueObjects\AuditFinding;
use Spatie\Activitylog\Models\Activity;

interface AuditRule
{
    public function evaluate(Activity $activity): ?AuditFinding;
}
