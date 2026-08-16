<?php

namespace AlizHarb\ActivityLog\Resources\ActivityLogs\Pages;

use AlizHarb\ActivityLog\Resources\ActivityLogs\ActivityLogResource;
use AlizHarb\ActivityLog\Support\AuditSchema;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListActivityLogs extends ListRecords
{
    protected static string $resource = ActivityLogResource::class;

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make(__('filament-activity-log::activity.table.tabs.all'))
                ->icon('heroicon-m-list-bullet'),
        ];

        $schema = app(AuditSchema::class);

        if (config('filament-activity-log.risk.enabled', true) && $schema->hasRiskColumns()) {
            $tabs['high_risk'] = Tab::make(__('filament-activity-log::activity.table.tabs.high_risk'))
                ->icon('heroicon-m-shield-exclamation')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereIn('risk_level', ['high', 'critical']));
        }

        if (config('filament-activity-log.retention.enabled', true) && $schema->hasRetentionHold()) {
            $tabs['retention_holds'] = Tab::make(__('filament-activity-log::activity.table.tabs.retention_holds'))
                ->icon('heroicon-m-lock-closed')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('retention_hold', true));
        }

        return $tabs;
    }
}
