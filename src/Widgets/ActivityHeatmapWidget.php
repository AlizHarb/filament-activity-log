<?php

namespace AlizHarb\ActivityLog\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class ActivityHeatmapWidget extends Widget
{
    /** @phpstan-ignore-next-line */
    protected string $view = 'filament-activity-log::widgets.activity-heatmap';

    protected int $days = 365;

    public function getData(): array
    {
        $driver = DB::getDriverName();
        $dateExpression = match ($driver) {
            'oracle' => 'TRUNC(created_at)',
            default => 'DATE(created_at)',
        };

        $data = Activity::query()
            ->select(
                DB::raw("$dateExpression as date"),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subDays($this->days))
            ->groupBy(DB::raw($dateExpression))
            ->get()
            ->pluck('count', 'date');

        return [
            'data' => $data,
            'max' => $data->max() ?: 1,
        ];
    }
}
