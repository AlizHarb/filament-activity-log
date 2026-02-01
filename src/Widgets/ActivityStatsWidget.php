<?php

namespace AlizHarb\ActivityLog\Widgets;

use AlizHarb\ActivityLog\Support\ActivityLogTitle;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class ActivityStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        /** @var (Activity&object{total: int})|null $topCauser */
        $topCauser = Activity::query()
            ->select('causer_id', 'causer_type', DB::raw('count(*) as total'))
            ->whereNotNull('causer_id')
            ->groupBy('causer_id', 'causer_type')
            ->orderByDesc('total')
            ->first();

        /** @var (Activity&object{total: int})|null $topSubject */
        $topSubject = Activity::query()
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

        return [
            Stat::make(__('filament-activity-log::activity.widgets.stats.total_activities'), Activity::count())
                ->description(__('filament-activity-log::activity.widgets.stats.total_description'))
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('info'),
            Stat::make(__('filament-activity-log::activity.widgets.stats.top_causer'), $causerLabel)
                ->description($topCauser ? __('filament-activity-log::activity.widgets.stats.top_causer_description', ['count' => $topCauser->total]) : __('filament-activity-log::activity.widgets.stats.no_data'))
                ->descriptionIcon('heroicon-m-user')
                ->color('success'),
            Stat::make(__('filament-activity-log::activity.widgets.stats.top_subject'), $subjectLabel)
                ->description($topSubject ? __('filament-activity-log::activity.widgets.stats.top_subject_description', ['count' => $topSubject->total]) : __('filament-activity-log::activity.widgets.stats.no_data'))
                ->descriptionIcon('heroicon-m-cube')
                ->color('warning'),
        ];
    }
}
