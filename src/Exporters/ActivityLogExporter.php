<?php

namespace AlizHarb\ActivityLog\Exporters;

use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Spatie\Activitylog\Models\Activity;

class ActivityLogExporter extends Exporter
{
    public static function getModel(): string
    {
        return Activity::class;
    }

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),

            ExportColumn::make('log_name')
                ->label(__('filament-activity-log::activity.table.column.log_name')),

            ExportColumn::make('description')
                ->label(__('filament-activity-log::activity.table.column.description')),

            ExportColumn::make('subject_type')
                ->label(__('filament-activity-log::activity.table.column.subject_type')),

            ExportColumn::make('subject_id')
                ->label(__('filament-activity-log::activity.table.column.subject_id')),

            ExportColumn::make('causer_type')
                ->label(__('filament-activity-log::activity.table.column.causer_type')),

            ExportColumn::make('causer_id')
                ->label(__('filament-activity-log::activity.table.column.causer_id')),

            ExportColumn::make('event')
                ->label(__('filament-activity-log::activity.table.column.event')),

            ExportColumn::make('properties.ip_address')
                ->label(__('filament-activity-log::activity.table.column.ip_address')),

            ExportColumn::make('properties.user_agent')
                ->label(__('filament-activity-log::activity.table.column.browser')),

            ExportColumn::make('created_at')
                ->label(__('filament-activity-log::activity.table.column.created_at')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = __('filament-activity-log::activity.action.export.notification.completed', [
            'successful_rows' => number_format($export->successful_rows),
            'rows_label' => str('row')->plural($export->successful_rows),
        ]);

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
