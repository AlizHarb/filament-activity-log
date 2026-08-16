<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Rules;

use AlizHarb\ActivityLog\Contracts\AuditRule;
use AlizHarb\ActivityLog\Support\ActivityRisk;
use AlizHarb\ActivityLog\ValueObjects\AuditFinding;
use Spatie\Activitylog\Models\Activity;

class HighRiskActivityRule implements AuditRule
{
    public function evaluate(Activity $activity): ?AuditFinding
    {
        $score = ActivityRisk::score($activity);
        $threshold = (int) config('filament-activity-log.alerts.high_risk_threshold', 55);

        if ($score < $threshold) {
            return null;
        }

        return new AuditFinding(
            title: 'High-risk activity detected',
            severity: ActivityRisk::level($activity),
            description: (string) $activity->description,
            context: ['risk_score' => $score],
        );
    }
}
