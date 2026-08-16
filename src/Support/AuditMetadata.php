<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Support;

use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

class AuditMetadata
{
    public function persist(Activity $activity, bool $refresh = true): void
    {
        if ($refresh && $activity->exists) {
            $activity->refresh();
        }

        $schema = app(AuditSchema::class);
        $updates = [];

        if ($schema->hasRiskColumns() && config('filament-activity-log.risk.enabled', true)) {
            app(RiskProjector::class)->project($activity);
            $updates['risk_score'] = $activity->getAttribute('risk_score');
            $updates['risk_level'] = $activity->getAttribute('risk_level');
        }

        if ($schema->hasContextColumns()) {
            $requestId = data_get($activity->properties, 'request_id');
            $ipAddress = data_get($activity->properties, 'ip_address');

            $updates['request_id'] = is_scalar($requestId) ? Str::substr((string) $requestId, 0, 100) : null;
            $updates['ip_address'] = is_scalar($ipAddress) ? Str::substr((string) $ipAddress, 0, 45) : null;

            $activity->setAttribute('request_id', $updates['request_id']);
            $activity->setAttribute('ip_address', $updates['ip_address']);
        }

        if ($schema->hasIntegrityHash() && config('filament-activity-log.integrity.enabled', true)) {
            $hash = app(ActivityIntegrity::class)->hash($activity);
            $activity->setAttribute('integrity_hash', $hash);
            $updates['integrity_hash'] = $hash;
        }

        if ($updates === []) {
            return;
        }

        $activity->newQueryWithoutScopes()
            ->whereKey($activity->getKey())
            ->toBase()
            ->update($updates);
    }
}
