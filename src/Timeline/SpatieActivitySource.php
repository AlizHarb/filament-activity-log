<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Timeline;

use AlizHarb\ActivityLog\Contracts\ProvidesTimelineActivities;
use AlizHarb\ActivityLog\Support\ActivityQuery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class SpatieActivitySource implements ProvidesTimelineActivities
{
    public function activitiesFor(Model $record, int $limit): Collection
    {
        return app(ActivityQuery::class)
            ->forRecord($record)
            ->with(['causer', 'subject'])
            ->latest()
            ->limit($limit)
            ->get();
    }
}
