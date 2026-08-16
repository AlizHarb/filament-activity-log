<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Support;

use Spatie\Activitylog\Models\Activity;

class ActivityRisk
{
    public static function score(Activity $activity): int
    {
        $persistedScore = $activity->getAttribute('risk_score');

        if (is_numeric($persistedScore)) {
            return min(max((int) $persistedScore, 0), 100);
        }

        return static::calculateScore($activity);
    }

    public static function calculateScore(Activity $activity): int
    {
        $resolver = config('filament-activity-log.risk.resolver');

        if (is_string($resolver) && class_exists($resolver)) {
            return (int) app($resolver)->score($activity);
        }

        $score = 0;

        $score += static::eventScore((string) $activity->event);
        $score += static::logNameScore((string) $activity->log_name);

        foreach (array_keys(ActivityChanges::getNewValues($activity) + ActivityChanges::getOldValues($activity)) as $field) {
            $score += static::fieldScore((string) $field);
        }

        if (! empty($activity->properties['ip_address']) && in_array('ip_address', config('filament-activity-log.risk.signals.context', []), true)) {
            $score += 5;
        }

        return min($score, 100);
    }

    public static function level(Activity $activity): string
    {
        $persistedLevel = $activity->getAttribute('risk_level');

        if (is_string($persistedLevel) && in_array($persistedLevel, ['low', 'medium', 'high', 'critical'], true)) {
            return $persistedLevel;
        }

        return static::levelForScore(static::score($activity));
    }

    public static function levelForScore(int $score): string
    {
        return match (true) {
            $score >= 80 => 'critical',
            $score >= 55 => 'high',
            $score >= 25 => 'medium',
            default => 'low',
        };
    }

    public static function color(Activity $activity): string
    {
        return match (static::level($activity)) {
            'critical' => 'danger',
            'high' => 'warning',
            'medium' => 'info',
            default => 'gray',
        };
    }

    public static function label(Activity $activity): string
    {
        return ucfirst(__('filament-activity-log::activity.risk.level.'.static::level($activity))).' ('.static::score($activity).')';
    }

    protected static function eventScore(string $event): int
    {
        return (int) (config('filament-activity-log.risk.events')[$event] ?? 0);
    }

    protected static function logNameScore(string $logName): int
    {
        return (int) (config('filament-activity-log.risk.log_names')[$logName] ?? 0);
    }

    protected static function fieldScore(string $field): int
    {
        foreach (config('filament-activity-log.risk.fields', []) as $pattern => $score) {
            if (@preg_match((string) $pattern, $field) === 1) {
                return (int) $score;
            }
        }

        return 0;
    }
}
