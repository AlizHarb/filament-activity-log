<?php

namespace AlizHarb\ActivityLog\Resources\ActivityLogs\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Class ActivityLogForm
 *
 * Defines the form schema for the Activity Log resource.
 * Currently, activity logs are typically read-only, so this form might be minimal or read-only.
 */
class ActivityLogForm
{
    /**
     * Configure the form.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('log_name')
                            ->label(__('filament-activity-log::activity.log_name'))
                            ->required()
                            ->disabled(),
                        TextInput::make('description')
                            ->label(__('filament-activity-log::activity.description'))
                            ->required()
                            ->disabled(),
                        TextInput::make('subject_type')
                            ->label(__('filament-activity-log::activity.subject_type'))
                            ->disabled(),
                        TextInput::make('subject_id')
                            ->label(__('filament-activity-log::activity.subject_id'))
                            ->disabled(),
                        TextInput::make('causer_type')
                            ->label(__('filament-activity-log::activity.causer_type'))
                            ->disabled(),
                        TextInput::make('causer_id')
                            ->label(__('filament-activity-log::activity.causer_id'))
                            ->disabled(),
                        KeyValue::make('properties')
                            ->label(__('filament-activity-log::activity.properties'))
                            ->disabled(),
                    ])
                    ->columns(2),
            ]);
    }
}
