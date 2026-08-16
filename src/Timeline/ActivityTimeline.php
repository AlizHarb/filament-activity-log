<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Timeline;

use AlizHarb\ActivityLog\Contracts\ProvidesTimelineActivities;
use AlizHarb\ActivityLog\Exceptions\InvalidConfigurationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;

class ActivityTimeline
{
    /**
     * @return Collection<int, Activity>
     */
    public function for(Model $record, ?int $limit = null): Collection
    {
        $limit ??= (int) config('filament-activity-log.timeline.limit', 50);
        $activities = collect();

        foreach (config('filament-activity-log.timeline.sources', [SpatieActivitySource::class]) as $sourceClass) {
            $source = app($sourceClass);

            if (! $source instanceof ProvidesTimelineActivities) {
                throw new InvalidConfigurationException('configuration.timeline_source', ['class' => $sourceClass]);
            }

            $activities = $activities->merge($source->activitiesFor($record, $limit));
        }

        return $activities
            ->unique(fn (Activity $activity): string => $activity::class.':'.$activity->getKey())
            ->sortByDesc('created_at')
            ->take($limit)
            ->values();
    }
}
