<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Support;

use AlizHarb\ActivityLog\Contracts\AuditRule;
use AlizHarb\ActivityLog\Events\AuditRuleMatched;
use AlizHarb\ActivityLog\Exceptions\InvalidConfigurationException;
use Spatie\Activitylog\Models\Activity;

class AuditRuleEngine
{
    public function evaluate(Activity $activity): void
    {
        if (! config('filament-activity-log.alerts.enabled', false)) {
            return;
        }

        foreach (config('filament-activity-log.alerts.rules', []) as $ruleClass) {
            $rule = app($ruleClass);

            if (! $rule instanceof AuditRule) {
                throw new InvalidConfigurationException('configuration.audit_rule', ['class' => $ruleClass]);
            }

            $finding = $rule->evaluate($activity);

            if ($finding) {
                AuditRuleMatched::dispatch($activity, $rule, $finding);
            }
        }
    }
}
