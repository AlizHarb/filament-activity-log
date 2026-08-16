<?php

namespace AlizHarb\ActivityLog\Widgets;

use AlizHarb\ActivityLog\Support\ActivityCache;
use AlizHarb\ActivityLog\Support\ActivityLogTitle;
use AlizHarb\ActivityLog\Support\ActivityQuery;
use AlizHarb\ActivityLog\Support\ActivityRisk;
use AlizHarb\ActivityLog\Support\AuditSchema;
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
        $data = app(ActivityCache::class)->remember('widget:stats', fn (): array => $this->calculateStats());

        $stats = [
            Stat::make(__('filament-activity-log::activity.widgets.stats.total_activities'), $data['total'])
                ->description(__('filament-activity-log::activity.widgets.stats.total_description'))
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('info'),
            Stat::make(__('filament-activity-log::activity.widgets.stats.top_causer'), $data['causer_label'])
                ->description($data['causer_count'] !== null ? trans_choice('filament-activity-log::activity.widgets.stats.top_causer_description', $data['causer_count']) : __('filament-activity-log::activity.widgets.stats.no_data'))
                ->descriptionIcon('heroicon-m-user')
                ->color('success'),
            Stat::make(__('filament-activity-log::activity.widgets.stats.top_subject'), $data['subject_label'])
                ->description($data['subject_count'] !== null ? trans_choice('filament-activity-log::activity.widgets.stats.top_subject_description', $data['subject_count']) : __('filament-activity-log::activity.widgets.stats.no_data'))
                ->descriptionIcon('heroicon-m-cube')
                ->color('warning'),
        ];

        if ($data['high_risk'] !== null) {
            $stats[] = Stat::make(__('filament-activity-log::activity.widgets.stats.high_risk'), $data['high_risk'])
                ->description(__('filament-activity-log::activity.widgets.stats.high_risk_description'))
                ->descriptionIcon('heroicon-m-shield-exclamation')
                ->color($data['high_risk'] > 0 ? 'danger' : 'success');
        }

        return $stats;
    }

    /**
     * @return array{total: int, causer_label: string, causer_count: int|null, subject_label: string, subject_count: int|null, high_risk: int|null}
     */
    protected function calculateStats(): array
    {
        $activityQuery = app(ActivityQuery::class);

        /** @var (Activity&object{total: int})|null $topCauser */
        $topCauser = $activityQuery->query()
            ->select('causer_id', 'causer_type', DB::raw('count(*) as total'))
            ->whereNotNull('causer_id')
            ->groupBy('causer_id', 'causer_type')
            ->orderByDesc('total')
            ->first();

        /** @var (Activity&object{total: int})|null $topSubject */
        $topSubject = $activityQuery->query()
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

        $highRiskCount = null;
        if (config('filament-activity-log.risk.enabled', true)) {
            $highRiskCount = app(AuditSchema::class)->hasRiskColumns()
                ? $activityQuery->query()->whereIn('risk_level', ['high', 'critical'])->count()
                : $activityQuery->query()
                    ->latest()
                    ->limit((int) config('filament-activity-log.widgets.stats.risk_sample_size', 500))
                    ->get()
                    ->filter(fn (Activity $activity): bool => in_array(ActivityRisk::level($activity), ['high', 'critical'], true))
                    ->count();

        }

        return [
            'total' => $activityQuery->query()->count(),
            'causer_label' => $causerLabel,
            'causer_count' => $topCauser ? (int) $topCauser->total : null,
            'subject_label' => $subjectLabel,
            'subject_count' => $topSubject ? (int) $topSubject->total : null,
            'high_risk' => $highRiskCount,
        ];
    }
}
