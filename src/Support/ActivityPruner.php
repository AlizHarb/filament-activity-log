<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Support;

use DateTimeInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

class ActivityPruner
{
    public function __construct(
        protected ActivityQuery $activities,
        protected AuditSchema $schema,
        protected ActivityCache $cache,
    ) {}

    /**
     * @throws AuthorizationException
     */
    public function pruneBefore(DateTimeInterface|string $cutoff): int
    {
        if (! config('filament-activity-log.mutations.enabled', false)
            || config('filament-activity-log.privacy.immutable_mode', false)) {
            throw new AuthorizationException((string) __('filament-activity-log::activity.exceptions.pruning.disabled'));
        }

        Gate::authorize('pruneAny', $this->activities->modelClass());

        $count = $this->activities->query()
            ->where('created_at', '<', $cutoff)
            ->when(
                $this->schema->hasRetentionHold(),
                fn (Builder $query): Builder => $query->where('retention_hold', false)
            )
            ->delete();

        if ($count > 0) {
            $this->cache->flush();
        }

        return $count;
    }
}
