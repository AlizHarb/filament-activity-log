<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

/**
 * Activity Chart Widget.
 *
 * Displays a line chart showing activity trends over time.
 * Configurable via config file for chart type, colors, time range, and more.
 */
class ActivityChartWidget extends ChartWidget
{
    /**
     * The sort order for the widget.
     */
    protected static ?int $sort = null;

    /**
     * Get the widget heading.
     *
     * @return string|null The heading text from config or default
     */
    public function getHeading(): ?string
    {
        return config('filament-activity-log.widgets.activity_chart.heading')
            ?? __('filament-activity-log::activity.widgets.activity_chart.heading');
    }

    /**
     * Get the maximum height for the chart.
     *
     * @return string|null The max height CSS value
     */
    protected function getMaxHeight(): ?string
    {
        return config('filament-activity-log.widgets.activity_chart.max_height', '300px');
    }

    /**
     * Get the sort order for the widget.
     *
     * @return int The sort order from config or default
     */
    public static function getSort(): int
    {
        return config('filament-activity-log.widgets.activity_chart.sort', 1);
    }

    /**
     * Get the polling interval for auto-refresh.
     *
     * @return string|null The polling interval (e.g., '10s', '1m') or null to disable
     */
    protected function getPollingInterval(): ?string
    {
        return config('filament-activity-log.widgets.activity_chart.polling_interval');
    }

    /**
     * Determine if the widget can be viewed.
     *
     * Checks if widgets are enabled globally and if this specific widget is enabled.
     *
     * @return bool True if the widget should be displayed
     */
    public static function canView(): bool
    {
        if (! config('filament-activity-log.widgets.enabled', true)) {
            return false;
        }

        if (! config('filament-activity-log.widgets.activity_chart.enabled', true)) {
            return false;
        }

        return true;
    }

    /**
     * Get the chart data.
     *
     * Retrieves activity counts grouped by date for the configured time range.
     * Returns data in Chart.js format with datasets and labels.
     *
     * @return array<string, mixed> Chart data array with 'datasets' and 'labels' keys
     */
    protected function getData(): array
    {
        $days = config('filament-activity-log.widgets.activity_chart.days', 30);
        $fillColor = config('filament-activity-log.widgets.activity_chart.fill_color', 'rgba(16, 185, 129, 0.1)');
        $borderColor = config('filament-activity-log.widgets.activity_chart.border_color', '#10b981');

        $driver = DB::getDriverName();

        $dateExpression = match ($driver) {
            'oracle' => 'TRUNC(created_at)',
            default => 'DATE(created_at)',
        };

        $data = Activity::query()
            ->select(
                DB::raw("$dateExpression as activity_date"),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy(DB::raw($dateExpression))
            ->orderBy(DB::raw($dateExpression))
            ->get()
            ->pluck('count', 'activity_date');

        return [
            'datasets' => [
                [
                    'label' => config('filament-activity-log.widgets.activity_chart.label', __('filament-activity-log::activity.widgets.activity_chart.label')),
                    'data' => $data->values()->toArray(),
                    'borderColor' => $borderColor,
                    'backgroundColor' => $fillColor,
                    'fill' => config('filament-activity-log.widgets.activity_chart.fill', true),
                    'tension' => config('filament-activity-log.widgets.activity_chart.tension', 0.3),
                ],
            ],
            'labels' => $data->keys()->map(fn ($date) => \Carbon\Carbon::parse($date)->format(
                config('filament-activity-log.widgets.activity_chart.date_format', 'M d')
            ))->toArray(),
        ];
    }

    /**
     * Get the chart type.
     *
     * @return string The chart type (e.g., 'line', 'bar', 'pie')
     */
    protected function getType(): string
    {
        return config('filament-activity-log.widgets.activity_chart.type', 'line');
    }

    /**
     * Get the chart options.
     *
     * Returns Chart.js configuration options for customizing the chart appearance.
     *
     * @return array<string, mixed> Chart.js options array
     */
    protected function getOptions(): array
    {
        return config('filament-activity-log.widgets.activity_chart.options', [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ]);
    }
}
