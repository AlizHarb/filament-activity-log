<?php

namespace AlizHarb\ActivityLog\Resources\ActivityLogs\Tables;

use AlizHarb\ActivityLog\Actions\ActivityLogTimelineTableAction;
use AlizHarb\ActivityLog\Enums\ActivityLogEvent;
use AlizHarb\ActivityLog\Exporters\ActivityLogExporter;
use AlizHarb\ActivityLog\Support\ActivityLogTitle;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ExportAction as FilamentExportAction;
use Filament\Actions\ViewAction;
use AlizHarb\ActivityLog\Models\Activity;
use AlizHarb\ActivityLog\Support\ActivityLogCauser;
use Filament\Facades\Filament;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

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
                    ->formatStateUsing(fn ($state) => ActivityLogEvent::tryFrom($state)?->getLabel() ?? ucfirst((string) $state))
                    ->color(fn ($state) => ActivityLogEvent::tryFrom($state)?->getColor() ?? 'gray')
                    ->icon(fn ($state) => ActivityLogEvent::tryFrom($state)?->getIcon())
                    ->searchable(config('filament-activity-log.table.columns.event.searchable', true))
                    ->sortable(config('filament-activity-log.table.columns.event.sortable', true))
                    ->visible(config('filament-activity-log.table.columns.event.visible', true)),

                TextColumn::make('subject_type')
                    ->label(__('filament-activity-log::activity.table.column.subject'))
                    ->formatStateUsing(fn ($state, $record) => ActivityLogTitle::get($record->subject))
                    ->description(fn ($record) => $record->subject_type)
                    ->url(function ($record) {
                        if (! $record->subject || ! function_exists('filament')) {
                            return null;
                        }

                        // Check for custom URL first
                        if ($customUrl = ActivityLogTitle::getUrl($record->subject)) {
                            return $customUrl;
                        }

                        $resource = Filament::getModelResource($record->subject_type);

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

                        // Check for custom URL first
                        if ($customUrl = ActivityLogTitle::getUrl($record->causer)) {
                            return $customUrl;
                        }

                        $resource = Filament::getModelResource(get_class($record->causer));

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
                    ->options(fn () => Activity::query()->distinct()->whereNotNull('log_name')->pluck('log_name', 'log_name')->toArray())
                    ->visible(config('filament-activity-log.table.filters.log_name', true)),

                SelectFilter::make('event')
                    ->label(__('filament-activity-log::activity.table.filter.event'))
                    ->options(ActivityLogEvent::class)
                    ->visible(config('filament-activity-log.table.filters.event', true)),

                SelectFilter::make('causer_id')
                    ->label(__('filament-activity-log::activity.table.filter.causer'))
                    ->options(function () {
                        $causerClass = ActivityLogCauser::resolveModelClass();
                        if (! $causerClass || ! class_exists($causerClass)) {
                            return [];
                        }

                        /** @var \Illuminate\Database\Eloquent\Builder $query */
                        $query = $causerClass::query();

                        return $query
                            ->whereIn('id', Activity::query()
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
                    ->options(fn () => Activity::query()
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

                Filter::make('batch_uuid')
                    ->label(__('filament-activity-log::activity.table.filter.batch'))
                    ->hidden()
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'] ?? null,
                        fn (Builder $query, $uuid): Builder => $query->where('batch_uuid', $uuid)
                    )),
            ])
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label(__('filament-activity-log::activity.filters')),
            )
            ->headerActions([
                Action::make('prune')
                    ->label(__('filament-activity-log::activity.action.prune.label'))
                    ->icon('heroicon-m-trash')
                    ->color('danger')
                    ->form([
                        DatePicker::make('prune_until')
                            ->label(__('filament-activity-log::activity.action.prune.date'))
                            ->default(now()->subDays(30))
                            ->required(),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading(__('filament-activity-log::activity.action.prune.heading'))
                    ->modalDescription(__('filament-activity-log::activity.action.prune.confirmation'))
                    ->action(function (array $data) {
                        $count = Activity::query()
                            ->where('created_at', '<', $data['prune_until'])
                            ->delete();

                        Notification::make()
                            ->success()
                            ->title(__('filament-activity-log::activity.action.prune.success', ['count' => $count]))
                            ->send();
                    })
                    ->visible(fn () => config('filament-activity-log.table.actions.prune', true) &&
                        (config('filament-activity-log.permissions.enabled') === false || Gate::allows('delete_any_activity'))
                    ),

                FilamentExportAction::make()
                    ->exporter(ActivityLogExporter::class)
                    ->icon('heroicon-m-arrow-down-tray')
                    ->color('gray')
                    ->visible(config('filament-activity-log.table.actions.export', true)),
            ])
            ->recordActions([
                ActionGroup::make([
                    ActivityLogTimelineTableAction::make()
                        ->visible(config('filament-activity-log.table.actions.timeline', true)),
                    ViewAction::make()
                        ->visible(config('filament-activity-log.table.actions.view', true)),

                    Action::make('view_batch')
                        ->label(__('filament-activity-log::activity.action.batch.label'))
                        ->icon('heroicon-m-rectangle-stack')
                        ->color('gray')
                        ->visible(fn ($record) => $record->batch_uuid &&
                            (config('filament-activity-log.permissions.enabled') === false || Gate::allows('view', $record))
                        )
                        ->url(fn ($record) => request()->url().'?tableFilters[batch_uuid][value]='.$record->batch_uuid),

                    Action::make('revert')
                        ->label(__('filament-activity-log::activity.action.revert.label'))
                        ->icon('heroicon-m-arrow-uturn-left')
                        ->color('warning')
                        ->schema(function ($record) {
                            $old = $record->properties['old'] ?? [];
                            $attributes = $record->properties['attributes'] ?? [];

                            $fields = [];
                            foreach ($old as $key => $value) {
                                $currentValue = data_get($attributes, $key);
                                $fields[] = Checkbox::make("revert_attributes.{$key}")
                                    ->label($key)
                                    ->helperText(__('filament-activity-log::activity.action.revert.helper_text', [
                                        'old' => $value,
                                        'new' => $currentValue,
                                    ]));
                            }

                            return $fields;
                        })
                        ->action(function ($record, array $data) {
                            $subject = $record->subject;
                            if (! $subject) {
                                Notification::make()->danger()->title(__('filament-activity-log::activity.action.revert.subject_not_found'))->send();

                                return;
                            }

                            $revertData = [];
                            $old = $record->properties['old'] ?? [];
                            foreach ($data['revert_attributes'] ?? [] as $key => $shouldRevert) {
                                if ($shouldRevert && isset($old[$key])) {
                                    $revertData[$key] = $old[$key];
                                }
                            }

                            if (empty($revertData)) {
                                Notification::make()->warning()->title(__('filament-activity-log::activity.action.revert.nothing_selected'))->send();

                                return;
                            }

                            $subject->update($revertData);
                            Notification::make()->success()->title(__('filament-activity-log::activity.action.revert.success'))->send();
                        })
                        ->visible(fn ($record) => config('filament-activity-log.table.actions.revert', true) &&
                            $record->event === 'updated' &&
                            $record->properties->has('old') &&
                            $record->subject !== null &&
                            (config('filament-activity-log.permissions.enabled') === false || Gate::allows('update', $record))
                        ),

                    Action::make('restore')
                        ->label(__('filament-activity-log::activity.action.restore.label'))
                        ->icon('heroicon-m-arrow-path')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading(__('filament-activity-log::activity.action.restore.heading'))
                        ->action(function ($record) {
                            $modelClass = $record->subject_type;
                            if (! $modelClass || ! class_exists($modelClass)) {
                                return;
                            }

                            $attributes = $record->properties['old'] ?? $record->properties['attributes'] ?? [];
                            if (empty($attributes)) {
                                return;
                            }

                            $modelClass::create($attributes);
                            Notification::make()->success()->title(__('filament-activity-log::activity.action.restore.success'))->send();
                        })
                        ->visible(fn ($record) => config('filament-activity-log.table.actions.restore', true) &&
                            $record->event === 'deleted' &&
                            $record->subject === null &&
                            (config('filament-activity-log.permissions.enabled') === false || Gate::allows('restore', $record))
                        ),
                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->modalHeading(__('filament-activity-log::activity.action.delete.heading'))
                        ->modalDescription(__('filament-activity-log::activity.action.delete.confirmation'))
                        ->modalSubmitActionLabel(__('filament-activity-log::activity.action.delete.button'))
                        ->visible(fn ($record) => config('filament-activity-log.table.actions.delete', true) &&
                            (config('filament-activity-log.permissions.enabled') === false || Gate::allows('delete', $record))
                        ),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalDescription(__('filament-activity-log::activity.action.bulk.delete.confirmation'))
                        ->visible(config('filament-activity-log.table.bulk_actions.delete', true)),

                    BulkAction::make('restore_selected')
                        ->label(__('filament-activity-log::activity.action.bulk.restore.label'))
                        ->icon('heroicon-m-arrow-path')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading(__('filament-activity-log::activity.action.bulk.restore.label'))
                        ->modalDescription(__('filament-activity-log::activity.action.bulk.restore.confirmation'))
                        ->action(function ($records) {
                            $restoredCount = 0;

                            foreach ($records as $record) {
                                if ($record->event !== 'deleted' || $record->subject !== null) {
                                    continue;
                                }

                                $modelClass = $record->subject_type;
                                if (! $modelClass || ! class_exists($modelClass)) {
                                    continue;
                                }

                                $attributes = $record->properties['old'] ?? $record->properties['attributes'] ?? [];
                                if (empty($attributes)) {
                                    continue;
                                }

                                $modelClass::create($attributes);
                                $restoredCount++;
                            }

                            if ($restoredCount > 0) {
                                Notification::make()
                                    ->success()
                                    ->title(__('filament-activity-log::activity.action.bulk.restore.success', ['count' => $restoredCount]))
                                    ->send();
                            }
                        })
                        ->visible(fn () => config('filament-activity-log.table.actions.restore', true)),

                    BulkAction::make('revert_selected')
                        ->label(__('filament-activity-log::activity.action.bulk.revert.label'))
                        ->icon('heroicon-m-arrow-uturn-left')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading(__('filament-activity-log::activity.action.bulk.revert.label'))
                        ->modalDescription(__('filament-activity-log::activity.action.bulk.revert.confirmation'))
                        ->action(function ($records) {
                            $revertedCount = 0;

                            foreach ($records as $record) {
                                if ($record->event !== 'updated' || ! $record->properties->has('old') || ! $record->subject) {
                                    continue;
                                }

                                $record->subject->update($record->properties['old']);
                                $revertedCount++;
                            }

                            if ($revertedCount > 0) {
                                Notification::make()
                                    ->success()
                                    ->title(__('filament-activity-log::activity.action.bulk.revert.success', ['count' => $revertedCount]))
                                    ->send();
                            }
                        })
                        ->visible(fn () => config('filament-activity-log.table.actions.revert', true)),
                ]),
            ]);
    }
}
