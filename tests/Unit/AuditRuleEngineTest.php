<?php

declare(strict_types=1);

use AlizHarb\ActivityLog\Events\AuditRuleMatched;
use Illuminate\Support\Facades\Event;
use Spatie\Activitylog\Models\Activity;

it('dispatches an extensible finding event for matching audit rules', function () {
    Event::fake([AuditRuleMatched::class]);
    config()->set('filament-activity-log.alerts.enabled', true);

    Activity::create([
        'log_name' => 'security',
        'event' => 'deleted',
        'description' => 'Deleted a protected record',
    ]);

    Event::assertDispatched(
        AuditRuleMatched::class,
        fn (AuditRuleMatched $event): bool => in_array($event->finding->severity, ['high', 'critical'], true)
            && $event->finding->context['risk_score'] >= 55
    );
});
