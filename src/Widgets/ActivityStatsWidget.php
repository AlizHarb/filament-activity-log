<?php

namespace AlizHarb\ActivityLog\Widgets;

use AlizHarb\ActivityLog\Support\ActivityLogTitle;
use AlizHarb\ActivityLog\Support\ActivityRisk;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class ActivityStatsWidget extends BaseWidget
{
    /**
     * Get the polling interval for auto-refresh.
     *
     * @return string|null The polling interval (e.g., '10s', '1m') or null to disable
     */
    protected function getPollingInterval(): ?string
    {
        return config('filament-activity-log.widgets.stats.polling_interval');
    }

    protected function getStats(): array
    {
        $activityModel = config('activitylog.activity_model') ?? Activity::class;

        /** @var (Activity&object{total: int})|null $topCauser */
        $topCauser = $activityModel::query()
            ->select('causer_id', 'causer_type', DB::raw('count(*) as total'))
            ->whereNotNull('causer_id')
            ->groupBy('causer_id', 'causer_type')
            ->orderByDesc('total')
            ->first();

        /** @var (Activity&object{total: int})|null $topSubject */
        $topSubject = $activityModel::query()
            ->select('subject_id', 'subject_type', DB::raw('count(*) as total'))
            ->whereNotNull('subject_id')
            ->groupBy('subject_id', 'subject_type')
            ->orderByDesc('total')
            ->first();

        $causerLabel = '-';
        if ($topCauser && ($causer = $topCauser->causer)) {
            $causerLabel = ActivityLogTitle::get($causer);
        }

        $subjectLabel = '-';
        if ($topSubject && ($subject = $topSubject->subject)) {
            $subjectLabel = ActivityLogTitle::get($subject);
        }

        $stats = [
            Stat::make(__('filament-activity-log::activity.widgets.stats.total_activities'), $activityModel::count())
                ->description(__('filament-activity-log::activity.widgets.stats.total_description'))
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('info'),
            Stat::make(__('filament-activity-log::activity.widgets.stats.top_causer'), $causerLabel)
                ->description($topCauser ? trans_choice('filament-activity-log::activity.widgets.stats.top_causer_description', $topCauser->total) : __('filament-activity-log::activity.widgets.stats.no_data'))
                ->descriptionIcon('heroicon-m-user')
                ->color('success'),
            Stat::make(__('filament-activity-log::activity.widgets.stats.top_subject'), $subjectLabel)
                ->description($topSubject ? trans_choice('filament-activity-log::activity.widgets.stats.top_subject_description', $topSubject->total) : __('filament-activity-log::activity.widgets.stats.no_data'))
                ->descriptionIcon('heroicon-m-cube')
                ->color('warning'),
        ];

        if (config('filament-activity-log.risk.enabled', true)) {
            $highRiskCount = $activityModel::query()
                ->latest()
                ->limit((int) config('filament-activity-log.widgets.stats.risk_sample_size', 500))
                ->get()
                ->filter(fn (Activity $activity): bool => in_array(ActivityRisk::level($activity), ['high', 'critical'], true))
                ->count();

            $stats[] = Stat::make(__('filament-activity-log::activity.widgets.stats.high_risk'), $highRiskCount)
                ->description(__('filament-activity-log::activity.widgets.stats.high_risk_description'))
                ->descriptionIcon('heroicon-m-shield-exclamation')
                ->color($highRiskCount > 0 ? 'danger' : 'success');
        }

        return $stats;
    }
}
