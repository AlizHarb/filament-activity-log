<?php

namespace AlizHarb\ActivityLog\Resources\ActivityLogs\Tables;

use AlizHarb\ActivityLog\Actions\ActivityLogTimelineTableAction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class ActivityLogTable
 *
 * Defines the table schema for the Activity Log resource.
 * Includes columns for log name, event, subject, causer, description, and creation date.
 * Also includes filters for log name, event, and date range.
 */
class ActivityLogTable
{
    /**
     * Configure the table.
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('log_name')
                    ->badge()
                    ->colors([
                        'gray' => 'default',
                        'info' => 'info',
                        'success' => 'success',
                        'warning' => 'warning',
                        'danger' => 'danger',
                    ])
                    ->label(__('filament-activity-log::activity.table.column.log_name'))
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->searchable(config('filament-activity-log.table.columns.log_name.searchable', true))
                    ->sortable(config('filament-activity-log.table.columns.log_name.sortable', true))
                    ->visible(config('filament-activity-log.table.columns.log_name.visible', true))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('event')
                    ->label(__('filament-activity-log::activity.table.column.event'))
                    ->badge()
                    ->colors([
                        'success' => 'created',
                        'warning' => 'updated',
                        'danger' => 'deleted',
                        'gray' => 'restored',
                    ])
                    ->searchable(config('filament-activity-log.table.columns.event.searchable', true))
                    ->sortable(config('filament-activity-log.table.columns.event.sortable', true))
                    ->visible(config('filament-activity-log.table.columns.event.visible', true)),

                TextColumn::make('subject_type')
                    ->label(__('filament-activity-log::activity.table.column.subject'))
                    ->formatStateUsing(function ($state, $record) {
                        if (! $state) {
                            return '-';
                        }

                        return class_basename($state).': '.($record->subject->name ?? $record->subject_id);
                    })
                    ->description(fn ($record) => $record->subject_type)
                    ->url(function ($record) {
                        if (! $record->subject || ! function_exists('filament')) {
                            return null;
                        }

                        $resource = \Filament\Facades\Filament::getModelResource($record->subject_type);

                        if ($resource && $resource::hasPage('view')) {
                            return $resource::getUrl('view', ['record' => $record->subject]);
                        }

                        if ($resource && $resource::hasPage('edit')) {
                            return $resource::getUrl('edit', ['record' => $record->subject]);
                        }

                        return null;
                    })
                    ->searchable(config('filament-activity-log.table.columns.subject_type.searchable', true))
                    ->sortable(config('filament-activity-log.table.columns.subject_type.sortable', true))
                    ->visible(config('filament-activity-log.table.columns.subject_type.visible', true))
                    ->toggleable(),

                TextColumn::make('causer.name')
                    ->label(__('filament-activity-log::activity.table.column.causer'))
                    ->description(fn ($record) => $record->causer?->email)
                    ->url(function ($record) {
                        if (! $record->causer || ! function_exists('filament')) {
                            return null;
                        }

                        $resource = \Filament\Facades\Filament::getModelResource(get_class($record->causer));

                        if ($resource && $resource::hasPage('view')) {
                            return $resource::getUrl('view', ['record' => $record->causer]);
                        }

                        if ($resource && $resource::hasPage('edit')) {
                            return $resource::getUrl('edit', ['record' => $record->causer]);
                        }

                        return null;
                    })
                    ->searchable(config('filament-activity-log.table.columns.causer.searchable', true))
                    ->sortable(config('filament-activity-log.table.columns.causer.sortable', true))
                    ->visible(config('filament-activity-log.table.columns.causer.visible', true))
                    ->toggleable(),

                TextColumn::make('properties.ip_address')
                    ->label(__('filament-activity-log::activity.table.column.ip_address'))
                    ->searchable(config('filament-activity-log.table.columns.ip_address.searchable', true))
                    ->visible(config('filament-activity-log.table.columns.ip_address.visible', true))
                    ->toggleable(),

                TextColumn::make('properties.user_agent')
                    ->label(__('filament-activity-log::activity.table.column.browser'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }

                        return $state;
                    })
                    ->searchable(config('filament-activity-log.table.columns.user_agent.searchable', true))
                    ->visible(config('filament-activity-log.table.columns.user_agent.visible', true))
                    ->toggleable(),

                TextColumn::make('description')
                    ->label(__('filament-activity-log::activity.table.column.description'))
                    ->limit(config('filament-activity-log.table.columns.description.limit', 50))
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= config('filament-activity-log.table.columns.description.limit', 50)) {
                            return null;
                        }

                        return $state;
                    })
                    ->searchable(config('filament-activity-log.table.columns.description.searchable', true))
                    ->visible(config('filament-activity-log.table.columns.description.visible', true))
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('created_at')
                    ->label(__('filament-activity-log::activity.table.column.created_at'))
                    ->dateTime(config('filament-activity-log.datetime_format', 'M d, Y H:i:s'))
                    ->searchable(config('filament-activity-log.table.columns.created_at.searchable', true))
                    ->sortable(config('filament-activity-log.table.columns.created_at.sortable', true))
                    ->visible(config('filament-activity-log.table.columns.created_at.visible', true))
                    ->toggleable(),
            ])
            ->defaultSort(
                config('filament-activity-log.resource.default_sort_column', 'created_at'),
                config('filament-activity-log.resource.default_sort_direction', 'desc')
            )
            ->filters([
                SelectFilter::make('log_name')
                    ->label(__('filament-activity-log::activity.table.column.log_name'))
                    ->options(fn () => \Spatie\Activitylog\Models\Activity::query()->distinct()->whereNotNull('log_name')->pluck('log_name', 'log_name')->toArray())
                    ->visible(config('filament-activity-log.table.filters.log_name', true)),

                SelectFilter::make('event')
                    ->label(__('filament-activity-log::activity.table.filter.event'))
                    ->options([
                        'created' => 'Created',
                        'updated' => 'Updated',
                        'deleted' => 'Deleted',
                        'restored' => 'Restored',
                    ])
                    ->visible(config('filament-activity-log.table.filters.event', true)),

                SelectFilter::make('causer_id')
                    ->label(__('filament-activity-log::activity.table.filter.causer'))
                    ->options(function () {
                        $causerClass = config('auth.providers.users.model');
                        if (! $causerClass || ! class_exists($causerClass)) {
                            return [];
                        }

                        return $causerClass::query()
                            ->whereIn('id', \Spatie\Activitylog\Models\Activity::query()
                                ->distinct()
                                ->whereNotNull('causer_id')
                                ->pluck('causer_id')
                            )
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->visible(config('filament-activity-log.table.filters.causer', true)),

                SelectFilter::make('subject_type')
                    ->label(__('filament-activity-log::activity.table.filter.subject_type'))
                    ->options(fn () => \Spatie\Activitylog\Models\Activity::query()
                        ->distinct()
                        ->whereNotNull('subject_type')
                        ->pluck('subject_type', 'subject_type')
                        ->mapWithKeys(fn ($type) => [$type => class_basename($type)])
                        ->toArray()
                    )
                    ->visible(config('filament-activity-log.table.filters.subject_type', true)),

                Filter::make('created_at')
                    ->label(__('filament-activity-log::activity.table.filter.created_at'))
                    ->form([
                        DatePicker::make('created_from')
                            ->label(__('filament-activity-log::activity.table.filter.created_from')),
                        DatePicker::make('created_until')
                            ->label(__('filament-activity-log::activity.table.filter.created_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->visible(config('filament-activity-log.table.filters.created_at', true)),
            ])
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label(__('filament-activity-log::activity.filters')),
            )
            ->headerActions([
                \Filament\Actions\ExportAction::make()
                    ->exporter(\AlizHarb\ActivityLog\Exporters\ActivityLogExporter::class)
                    ->icon('heroicon-m-arrow-down-tray')
                    ->color('gray')
                    ->visible(config('filament-activity-log.table.actions.export', true)),
            ])
            ->recordActions([
                \Filament\Actions\ActionGroup::make([
                    ActivityLogTimelineTableAction::make()
                        ->visible(config('filament-activity-log.table.actions.timeline', true)),
                    ViewAction::make()
                        ->visible(config('filament-activity-log.table.actions.view', true)),
                    Action::make('revert')
                        ->icon('heroicon-m-arrow-uturn-left')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading(__('filament-activity-log::activity.action.revert.heading'))
                        ->modalDescription(__('filament-activity-log::activity.action.revert.confirmation'))
                        ->modalSubmitActionLabel(__('filament-activity-log::activity.action.revert.button'))
                        ->action(function (\Spatie\Activitylog\Models\Activity $record) {
                            // Check if the activity has old values
                            if (! $record->properties->has('old')) {
                                \Filament\Notifications\Notification::make()
                                    ->warning()
                                    ->title(__('filament-activity-log::activity.action.revert.no_old_data'))
                                    ->send();

                                return;
                            }

                            // Get the subject model
                            $subject = $record->subject;
                            if (! $subject) {
                                \Filament\Notifications\Notification::make()
                                    ->danger()
                                    ->title(__('filament-activity-log::activity.action.revert.subject_not_found'))
                                    ->send();

                                return;
                            }

                            // Revert the changes
                            $subject->update($record->properties['old']);

                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title(__('filament-activity-log::activity.action.revert.success'))
                                ->send();
                        })
                        ->visible(fn (\Spatie\Activitylog\Models\Activity $record) => config('filament-activity-log.table.actions.revert', true) &&
                            $record->event === 'updated' &&
                            $record->properties->has('old') &&
                            $record->subject !== null
                        ),
                    \Filament\Actions\DeleteAction::make()
                        ->requiresConfirmation()
                        ->modalHeading(__('filament-activity-log::activity.action.delete.heading'))
                        ->modalDescription(__('filament-activity-log::activity.action.delete.confirmation'))
                        ->modalSubmitActionLabel(__('filament-activity-log::activity.action.delete.button'))
                        ->visible(config('filament-activity-log.table.actions.delete', true)),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(config('filament-activity-log.table.bulk_actions.delete', true)),
                ]),
            ]);
    }
}
