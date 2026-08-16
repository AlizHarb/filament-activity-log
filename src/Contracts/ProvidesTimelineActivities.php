<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;

interface ProvidesTimelineActivities
{
    /**
     * @return Collection<int, Activity>
     */
    public function activitiesFor(Model $record, int $limit): Collection;
}
