<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Resources\ActivityLogs;

use AlizHarb\ActivityLog\ActivityLogPlugin;
use AlizHarb\ActivityLog\Resources\ActivityLogs\Schemas\ActivityLogForm;
use AlizHarb\ActivityLog\Resources\ActivityLogs\Schemas\ActivityLogInfolist;
use AlizHarb\ActivityLog\Resources\ActivityLogs\Tables\ActivityLogTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;
use UnitEnum;

/**
 * Class ActivityLogResource
 *
 * The Filament resource for managing and viewing activity logs.
 */
class ActivityLogResource extends Resource
{
    /**
     * The model class associated with this resource.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>|null
     */
    protected static ?string $model = Activity::class;

    public static function getLabel(): ?string
    {
        try {
            return ActivityLogPlugin::get()->getLabel();
        } catch (\Throwable $e) {
            return config('filament-activity-log.resource.label');
        }
    }

    public static function getPluralLabel(): ?string
    {
        try {
            return ActivityLogPlugin::get()->getPluralLabel();
        } catch (\Throwable $e) {
            return config('filament-activity-log.resource.plural_label');
        }
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        try {
            return ActivityLogPlugin::get()->getNavigationGroup();
        } catch (\Throwable $e) {
            return config('filament-activity-log.resource.group');
        }
    }

    public static function getCluster(): ?string
    {
        try {
            return ActivityLogPlugin::get()->getCluster();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public static function getNavigationSort(): ?int
    {
        try {
            return ActivityLogPlugin::get()->getNavigationSort();
        } catch (\Throwable $e) {
            return config('filament-activity-log.resource.sort');
        }
    }

    public static function getNavigationIcon(): ?string
    {
        try {
            return ActivityLogPlugin::get()->getNavigationIcon() ?? parent::getNavigationIcon();
        } catch (\Throwable $e) {
            return config('filament-activity-log.resource.navigation_icon') ?? parent::getNavigationIcon();
        }
    }

    public static function getNavigationBadge(): ?string
    {
        try {
            $badge = ActivityLogPlugin::get()->getNavigationCountBadge();
        } catch (\Throwable $e) {
            $badge = null;
        }

        if ($badge !== null) {
            return $badge;
        }

        return config('filament-activity-log.resource.navigation_count_badge') ? number_format(static::getModel()::count()) : null;
    }

    public static function getGlobalSearchEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['causer', 'subject']);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['causer', 'subject']);
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        /** @var \Spatie\Activitylog\Models\Activity $record */
        $details = [];

        if ($record->causer) {
            $details['Causer'] = data_get($record->causer, 'name');
        }

        if ($record->subject) {
            $details['Subject'] = \AlizHarb\ActivityLog\Support\ActivityLogTitle::get($record->subject);
        }

        return $details;
    }

    public static function getGlobalSearchAttributes(): array
    {
        return config('filament-activity-log.resource.global_search.attributes', []);
    }

    /**
     * Define the form schema.
     */
    public static function form(Schema $schema): Schema
    {
        return ActivityLogForm::configure($schema);
    }

    /**
     * Define the table schema.
     */
    public static function table(Table $table): Table
    {
        return ActivityLogTable::configure($table);
    }

    /**
     * Define the infolist schema.
     */
    public static function infolist(Schema $schema): Schema
    {
        return ActivityLogInfolist::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
            'view' => Pages\ViewActivityLog::route('/{record}'),
        ];
    }
}
