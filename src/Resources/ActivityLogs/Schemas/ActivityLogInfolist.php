<?php

namespace AlizHarb\ActivityLog\Resources\ActivityLogs\Schemas;

use Filament\Facades\Filament;
use Filament\Infolists\Components\CodeEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

/**
 * Class ActivityLogInfolist
 *
 * Defines the infolist schema for the Activity Log resource.
 * Displays detailed information about an activity, including overview, changes, and raw data.
 */
class ActivityLogInfolist
{
    /**
     * Configure the infolist.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('Activity Details')
                    ->tabs([
                        Tab::make('Overview')
                            ->label(__('filament-activity-log::activity.infolist.tab.overview'))
                            ->icon('heroicon-m-information-circle')
                            ->visible(config('filament-activity-log.infolist.tabs.overview', true))
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Group::make([
                                            TextEntry::make('log_name')
                                                ->badge()
                                                ->label(__('filament-activity-log::activity.infolist.entry.log_name'))
                                                ->visible(config('filament-activity-log.infolist.entries.log_name', true)),

                                            TextEntry::make('event')
                                                ->badge()
                                                ->label(__('filament-activity-log::activity.infolist.entry.event'))
                                                ->formatStateUsing(fn ($state) => ucfirst($state))
                                                ->colors([
                                                    'success' => 'created',
                                                    'warning' => 'updated',
                                                    'danger' => 'deleted',
                                                    'gray' => 'restored',
                                                ])
                                                ->visible(config('filament-activity-log.infolist.entries.event', true)),

                                            TextEntry::make('created_at')
                                                ->label(__('filament-activity-log::activity.infolist.entry.created_at'))
                                                ->dateTime()
                                                ->visible(config('filament-activity-log.infolist.entries.created_at', true)),
                                        ]),

                                        Group::make([
                                            TextEntry::make('causer')
                                                ->label(__('filament-activity-log::activity.infolist.entry.causer'))
                                                ->getStateUsing(fn ($record) => $record->causer->name ?? 'System')
                                                ->url(function ($record) {
                                                    $causer = $record->causer;
                                                    if (! $causer) {
                                                        return null;
                                                    }
                                                    $resource = Filament::getModelResource($causer);
                                                    if (! $resource) {
                                                        return null;
                                                    }

                                                    if (! array_key_exists('view', $resource::getPages())) {
                                                        return null;
                                                    }

                                                    return $resource::getUrl('view', ['record' => $causer]);
                                                })
                                                ->visible(config('filament-activity-log.infolist.entries.causer', true)),

                                            TextEntry::make('subject')
                                                ->label(__('filament-activity-log::activity.infolist.entry.subject'))
                                                ->getStateUsing(fn ($record) => $record->subject->name ?? $record->subject->title ?? class_basename($record->subject_type))
                                                ->url(function ($record) {
                                                    $subject = $record->subject;
                                                    if (! $subject) {
                                                        return null;
                                                    }
                                                    $resource = Filament::getModelResource($subject);
                                                    if (! $resource) {
                                                        return null;
                                                    }

                                                    if (! array_key_exists('view', $resource::getPages())) {
                                                        return null;
                                                    }

                                                    return $resource::getUrl('view', ['record' => $subject]);
                                                })
                                                ->visible(config('filament-activity-log.infolist.entries.subject', true)),

                                            TextEntry::make('properties.ip_address')
                                                ->label(__('filament-activity-log::activity.infolist.entry.ip_address'))
                                                ->visible(config('filament-activity-log.infolist.entries.ip_address', true)),

                                            TextEntry::make('properties.user_agent')
                                                ->label(__('filament-activity-log::activity.infolist.entry.browser'))
                                                ->visible(config('filament-activity-log.infolist.entries.user_agent', true)),
                                        ]),
                                    ]),

                                TextEntry::make('description')
                                    ->label(__('filament-activity-log::activity.infolist.entry.description'))
                                    ->columnSpanFull()
                                    ->visible(config('filament-activity-log.infolist.entries.description', true)),
                            ]),

                        Tab::make('Changes')
                            ->label(__('filament-activity-log::activity.infolist.tab.changes'))
                            ->icon('heroicon-m-arrows-right-left')
                            ->visible(config('filament-activity-log.infolist.tabs.changes', true))
                            ->schema([
                                KeyValueEntry::make('properties.attributes')
                                    ->label(__('filament-activity-log::activity.infolist.entry.attributes'))
                                    ->keyLabel(__('filament-activity-log::activity.infolist.entry.key'))
                                    ->valueLabel(__('filament-activity-log::activity.infolist.entry.value'))
                                    ->visible(fn ($record) => $record->properties->has('attributes') && config('filament-activity-log.infolist.entries.properties_attributes', true)),

                                KeyValueEntry::make('properties.old')
                                    ->label(__('filament-activity-log::activity.infolist.entry.old'))
                                    ->keyLabel(__('filament-activity-log::activity.infolist.entry.key'))
                                    ->valueLabel(__('filament-activity-log::activity.infolist.entry.value'))
                                    ->visible(fn ($record) => $record->properties->has('old') && config('filament-activity-log.infolist.entries.properties_old', true)),
                            ]),

                        Tab::make('Raw Data')
                            ->label(__('filament-activity-log::activity.infolist.tab.raw_data'))
                            ->icon('heroicon-m-code-bracket')
                            ->visible(config('filament-activity-log.infolist.tabs.raw_data', true))
                            ->schema([
                                CodeEntry::make('properties')
                                    ->label(__('filament-activity-log::activity.infolist.entry.properties'))
                                    ->formatStateUsing(fn ($state) => json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))
                                    ->columnSpanFull()
                                    ->visible(config('filament-activity-log.infolist.entries.properties_raw', true)),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
