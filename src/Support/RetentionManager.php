<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Support;

use AlizHarb\ActivityLog\Events\RetentionHoldChanged;
use AlizHarb\ActivityLog\Exceptions\RetentionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Spatie\Activitylog\Models\Activity;

class RetentionManager
{
    public function __construct(
        protected ActivityQuery $activities,
        protected AuditSchema $schema,
        protected ActivityIntegrity $integrity,
        protected ActivityCache $cache,
    ) {}

    public function setHold(Activity $activity, bool $held): bool
    {
        if (! config('filament-activity-log.retention.enabled', true) || ! $this->schema->hasRetentionHold()) {
            throw new RetentionException('retention.unavailable');
        }

        Gate::authorize('hold', $activity);

        $changed = DB::connection($activity->getConnectionName())->transaction(function () use ($activity, $held): bool {
            /** @var Activity|null $locked */
            $locked = $this->activities->query()
                ->whereKey($activity->getKey())
                ->lockForUpdate()
                ->first();

            if (! $locked) {
                throw new RetentionException('retention.outside_boundary');
            }

            if ((bool) $locked->getAttribute('retention_hold') === $held) {
                return false;
            }

            // Keep the signed representation identical across database drivers.
            $locked->setAttribute('retention_hold', $held ? 1 : 0);
            $updates = ['retention_hold' => $held];

            if ($this->schema->hasIntegrityHash() && config('filament-activity-log.integrity.enabled', true)) {
                $updates['integrity_hash'] = $this->integrity->hash($locked);
            }

            $locked->newQueryWithoutScopes()
                ->whereKey($locked->getKey())
                ->toBase()
                ->update($updates);

            $this->logControlActivity($locked, $held);

            return true;
        });

        if (! $changed) {
            return false;
        }

        $activity->refresh();
        $this->cache->flush();
        RetentionHoldChanged::dispatch($activity, $held);

        return true;
    }

    protected function logControlActivity(Activity $activity, bool $held): void
    {
        if (! config('filament-activity-log.retention.log_activity', true)) {
            return;
        }

        $logger = activity(config('filament-activity-log.mutations.log_name', 'audit-control'))
            ->performedOn($activity)
            ->event($held ? 'retention_hold_placed' : 'retention_hold_released')
            ->withProperties([
                'target_activity_id' => $activity->getKey(),
                'retention_hold' => $held,
            ]);

        $user = Auth::user();
        if ($user instanceof Model) {
            $logger->causedBy($user);
        }

        $logger->log($held ? 'Retention hold placed' : 'Retention hold released');
    }
}
