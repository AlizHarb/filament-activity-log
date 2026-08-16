<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Support;

use Spatie\Activitylog\Models\Activity;

class RiskProjector
{
    public function project(Activity $activity): Activity
    {
        if (! config('filament-activity-log.risk.enabled', true) || ! app(AuditSchema::class)->hasRiskColumns()) {
            return $activity;
        }

        $score = ActivityRisk::calculateScore($activity);

        $activity->setAttribute('risk_score', $score);
        $activity->setAttribute('risk_level', ActivityRisk::levelForScore($score));

        return $activity;
    }
}
