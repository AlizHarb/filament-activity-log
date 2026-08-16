<?php

namespace AlizHarb\ActivityLog\Widgets;

use AlizHarb\ActivityLog\Support\ActivityCache;
use AlizHarb\ActivityLog\Support\ActivityQuery;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class ActivityHeatmapWidget extends Widget
{
    /** @phpstan-ignore-next-line */
    protected string $view = 'filament-activity-log::widgets.activity-heatmap';

    protected int $days = 365;

    /**
     * @return array{data: array<string, int>, max: int}
     */
    public function getData(): array
    {
        $activityQuery = app(ActivityQuery::class);
        $driver = $activityQuery->driverName();
        $dateExpression = match ($driver) {
            'oracle' => 'TRUNC(created_at)',
            default => 'DATE(created_at)',
        };

        $data = app(ActivityCache::class)->remember("widget:heatmap:{$this->days}", fn (): array => $activityQuery->query()
            ->select(
                DB::raw("$dateExpression as date"),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subDays($this->days))
            ->groupBy(DB::raw($dateExpression))
            ->get()
            ->pluck('count', 'date')
            ->map(fn (mixed $count): int => (int) $count)
            ->toArray());

        return [
            'data' => $data,
            'max' => max($data ?: [1]),
        ];
    }
}
