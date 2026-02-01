<?php

namespace AlizHarb\ActivityLog\Pages;

use AlizHarb\ActivityLog\ActivityLogPlugin;
use AlizHarb\ActivityLog\Widgets\ActivityChartWidget;
use AlizHarb\ActivityLog\Widgets\ActivityHeatmapWidget;
use AlizHarb\ActivityLog\Widgets\ActivityStatsWidget;
use Filament\Pages\Page;

class AuditDashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-bar';

    protected string $view = 'filament-activity-log::pages.audit-dashboard';

    public static function getNavigationGroup(): ?string
    {
        try {
            return ActivityLogPlugin::get()->getNavigationGroup();
        } catch (\Throwable $e) {
            return config('filament-activity-log.dashboard.navigation_group')
                ?? config('filament-activity-log.resource.group');
        }
    }

    public static function getNavigationLabel(): string
    {
        try {
            return ActivityLogPlugin::get()->getDashboardTitle();
        } catch (\Throwable $e) {
            return config('filament-activity-log.dashboard.title')
                ?? __('filament-activity-log::activity.pages.audit_dashboard.title');
        }
    }

    public function getHeading(): string
    {
        return static::getNavigationLabel();
    }

    public static function getNavigationSort(): ?int
    {
        return config('filament-activity-log.dashboard.navigation_sort') ?? 0;
    }

    public static function shouldRegisterNavigation(): bool
    {
        try {
            return ActivityLogPlugin::get()->isDashboardEnabled();
        } catch (\Throwable $e) {
            return config('filament-activity-log.dashboard.enabled', false);
        }
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ActivityStatsWidget::class,
            ActivityChartWidget::class,
            ActivityHeatmapWidget::class,
        ];
    }
}
