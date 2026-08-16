<?php

namespace AlizHarb\ActivityLog\Resources\ActivityLogs\Tables;

use AlizHarb\ActivityLog\Actions\ActivityLogTimelineTableAction;
use AlizHarb\ActivityLog\ActivityLogPlugin;
use AlizHarb\ActivityLog\Enums\ActivityLogEvent;
use AlizHarb\ActivityLog\Exporters\ActivityLogExporter;
use AlizHarb\ActivityLog\Support\ActivityCache;
use AlizHarb\ActivityLog\Support\ActivityGrouping;
use AlizHarb\ActivityLog\Support\ActivityLogCauser;
use AlizHarb\ActivityLog\Support\ActivityLogRedactor;
use AlizHarb\ActivityLog\Support\ActivityLogTitle;
use AlizHarb\ActivityLog\Support\ActivityMutation;
use AlizHarb\ActivityLog\Support\ActivityPruner;
use AlizHarb\ActivityLog\Support\ActivityQuery;
use AlizHarb\ActivityLog\Support\ActivityRisk;
use AlizHarb\ActivityLog\Support\AuditSchema;
use AlizHarb\ActivityLog\Support\RetentionManager;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ExportAction as FilamentExportAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

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
        $table = $table
            ->deferLoading()
            ->deferFilters()
            ->searchOnBlur()
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->persistSortInSession()
            ->reorderableColumns()
            ->columnManagerColumns(2)
            ->paginated(config('filament-activity-log.resource.pagination.options', [10, 25, 50, 100]))
            ->defaultPaginationPageOption(config('filament-activity-log.resource.pagination.default', 50))
            ->extremePaginationLinks()
            ->striped()
            ->emptyStateIcon('heroicon-o-clipboard-document-list')
            ->emptyStateHeading(__('filament-activity-log::activity.table.empty.heading'))
            ->emptyStateDescription(__('filament-activity-log::activity.table.empty.description'))
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

                TextColumn::make('risk')
                    ->label(__('filament-activity-log::activity.table.column.risk'))
                    ->badge()
                    ->state(fn ($record) => ActivityRisk::label($record))
                    ->color(fn ($record) => ActivityRisk::color($record))
                    ->visible(fn () => config('filament-activity-log.risk.enabled', true) &&
                        config('filament-activity-log.table.columns.risk.visible', true)
                    )
                    ->sortable(
                        app(AuditSchema::class)->hasRiskColumns() && config('filament-activity-log.table.columns.risk.sortable', true),
                        query: fn (Builder $query, string $direction): Builder => $query->orderBy('risk_score', $direction === 'asc' ? 'asc' : 'desc')
                    )
                    ->toggleable(),

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

                        $morphedModel = Relation::getMorphedModel($record->subject_type);
                        $modelClass = is_string($morphedModel) ? $morphedModel : $record->subject_type;

                        if (! is_string($modelClass)) {
                            return null;
                        }

                        $resource = Filament::getModelResource($modelClass);

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

                TextColumn::make('subject_id')
                    ->label(__('filament-activity-log::activity.table.column.subject_id'))
                    ->searchable(config('filament-activity-log.table.columns.subject_id.searchable', true))
                    ->sortable(config('filament-activity-log.table.columns.subject_id.sortable', true))
                    ->visible(config('filament-activity-log.table.columns.subject_id.visible', true))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('causer.'.config('filament-activity-log.causer.display_attribute', 'name'))
                    ->label(__('filament-activity-log::activity.table.column.causer'))
                    ->description(fn ($record) => $record->causer?->email)
                    ->url(function ($record) {
                        $causer = $record->causer;

                        if (! $causer instanceof Model || ! function_exists('filament')) {
                            return null;
                        }

                        // Check for custom URL first
                        if ($customUrl = ActivityLogTitle::getUrl($causer)) {
                            return $customUrl;
                        }

                        $resource = Filament::getModelResource($causer);

                        if ($resource && $resource::hasPage('view')) {
                            return $resource::getUrl('view', ['record' => $causer]);
                        }

                        if ($resource && $resource::hasPage('edit')) {
                            return $resource::getUrl('edit', ['record' => $causer]);
                        }

                        return null;
                    })
                    ->searchable(config('filament-activity-log.table.columns.causer.searchable', true))
                    ->sortable(config('filament-activity-log.table.columns.causer.sortable', true))
                    ->visible(config('filament-activity-log.table.columns.causer.visible', true))
                    ->toggleable(),

                TextColumn::make('ip_address')
                    ->label(__('filament-activity-log::activity.table.column.ip_address'))
                    ->state(fn ($record) => $record->getAttribute('ip_address') ?? data_get($record->properties, 'ip_address'))
                    ->searchable(
                        app(AuditSchema::class)->hasContextColumns()
                            && config('filament-activity-log.table.columns.ip_address.searchable', true)
                    )
                    ->visible(config('filament-activity-log.table.columns.ip_address.visible', true))
                    ->toggleable(),

                TextColumn::make('request_id')
                    ->label(__('filament-activity-log::activity.table.column.request_id'))
                    ->state(fn ($record) => $record->getAttribute('request_id') ?? data_get($record->properties, 'request_id'))
                    ->searchable(app(AuditSchema::class)->hasContextColumns())
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('properties.user_agent')
                    ->label(__('filament-activity-log::activity.table.column.browser'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (Str::length($state) <= 50) {
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
                        if (Str::length($state) <= config('filament-activity-log.table.columns.description.limit', 50)) {
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
                    ->options(fn () => app(ActivityCache::class)->remember(
                        'filters:log-names',
                        fn (): array => app(ActivityQuery::class)->query()->distinct()->whereNotNull('log_name')->pluck('log_name', 'log_name')->toArray()
                    ))
                    ->visible(config('filament-activity-log.table.filters.log_name', true)),

                SelectFilter::make('event')
                    ->label(__('filament-activity-log::activity.table.filter.event'))
                    ->options(ActivityLogEvent::class)
                    ->visible(config('filament-activity-log.table.filters.event', true)),

                SelectFilter::make('risk_level')
                    ->label(__('filament-activity-log::activity.table.column.risk'))
                    ->options([
                        'low' => __('filament-activity-log::activity.risk.level.low'),
                        'medium' => __('filament-activity-log::activity.risk.level.medium'),
                        'high' => __('filament-activity-log::activity.risk.level.high'),
                        'critical' => __('filament-activity-log::activity.risk.level.critical'),
                    ])
                    ->multiple()
                    ->visible(config('filament-activity-log.table.filters.risk', true) && app(AuditSchema::class)->hasRiskColumns()),

                Filter::make('retention_hold')
                    ->label(__('filament-activity-log::activity.table.filter.retention_hold'))
                    ->query(fn (Builder $query): Builder => $query->where('retention_hold', true))
                    ->visible(config('filament-activity-log.table.filters.retention_hold', true) && app(AuditSchema::class)->hasRetentionHold()),

                SelectFilter::make('causer_id')
                    ->label(__('filament-activity-log::activity.table.filter.causer'))
                    ->options(function () {
                        return app(ActivityCache::class)->remember('filters:causers', function (): array {
                            $causerClass = ActivityLogCauser::resolveModelClass();
                            if (! $causerClass || ! class_exists($causerClass)) {
                                return [];
                            }

                            $query = $causerClass::query()->whereIn('id', app(ActivityQuery::class)->query()
                                ->distinct()
                                ->whereNotNull('causer_id')
                                ->pluck('causer_id')
                            );

                            return ActivityLogCauser::pluckOptions($query);
                        });
                    })
                    ->searchable()
                    ->visible(config('filament-activity-log.table.filters.causer', true)),

                SelectFilter::make('subject_type')
                    ->label(__('filament-activity-log::activity.table.filter.subject_type'))
                    ->options(fn () => app(ActivityCache::class)->remember(
                        'filters:subject-types',
                        fn (): array => app(ActivityQuery::class)->query()
                            ->distinct()
                            ->whereNotNull('subject_type')
                            ->pluck('subject_type', 'subject_type')
                            ->mapWithKeys(fn ($type) => [$type => class_basename(Relation::getMorphedModel($type) ?? $type)])
                            ->toArray()
                    ))
                    ->visible(config('filament-activity-log.table.filters.subject_type', true)),

                Filter::make('subject_id')
                    ->label(__('filament-activity-log::activity.table.column.subject_id'))
                    ->form([
                        TextInput::make('value')
                            ->label(__('filament-activity-log::activity.table.column.subject_id'))
                            ->numeric(),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        filled($data['value'] ?? null),
                        fn (Builder $query): Builder => $query->where('subject_id', $data['value'])
                    ))
                    ->visible(config('filament-activity-log.table.filters.subject_id', true)),

                Filter::make('request_id')
                    ->label(__('filament-activity-log::activity.table.filter.request_id'))
                    ->form([
                        TextInput::make('value')
                            ->label(__('filament-activity-log::activity.table.filter.request_id')),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        filled($data['value'] ?? null),
                        fn (Builder $query): Builder => app(AuditSchema::class)->hasContextColumns()
                            ? $query->where('request_id', $data['value'])
                            : $query->where('properties->request_id', $data['value'])
                    ))
                    ->visible(config('filament-activity-log.table.filters.request_id', true)),

                Filter::make('ip_address')
                    ->label(__('filament-activity-log::activity.table.column.ip_address'))
                    ->form([
                        TextInput::make('value')
                            ->label(__('filament-activity-log::activity.table.column.ip_address')),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        filled($data['value'] ?? null),
                        fn (Builder $query): Builder => app(AuditSchema::class)->hasContextColumns()
                            ? $query->where('ip_address', $data['value'])
                            : $query->where('properties->ip_address', $data['value'])
                    ))
                    ->visible(config('filament-activity-log.table.filters.ip_address', true)),

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
                        fn (Builder $query, $groupId): Builder => ActivityGrouping::applyGroupFilter($query, $groupId)
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
                        $count = app(ActivityPruner::class)->pruneBefore($data['prune_until']);

                        Notification::make()
                            ->success()
                            ->title(__('filament-activity-log::activity.action.prune.success', ['count' => $count]))
                            ->send();
                    })
                    ->visible(fn () => config('filament-activity-log.table.actions.prune', true) &&
                        config('filament-activity-log.mutations.enabled', false) &&
                        ! config('filament-activity-log.privacy.immutable_mode', false) &&
                        Gate::allows('pruneAny', app(ActivityQuery::class)->modelClass())
                    ),

                FilamentExportAction::make()
                    ->exporter(ActivityLogExporter::class)
                    ->icon('heroicon-m-arrow-down-tray')
                    ->color('gray')
                    ->visible(fn () => config('filament-activity-log.table.actions.export', true)
                        && Gate::allows('exportAny', app(ActivityQuery::class)->modelClass())),
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
                        ->visible(fn ($record) => ActivityGrouping::hasGroup($record) &&
                            (config('filament-activity-log.permissions.enabled') === false || Gate::allows('view', $record))
                        )
                        ->url(fn ($record) => request()->url().'?tableFilters[batch_uuid][value]='.ActivityGrouping::getGroupId($record)),

                    Action::make('revert')
                        ->label(__('filament-activity-log::activity.action.revert.label'))
                        ->icon('heroicon-m-arrow-uturn-left')
                        ->color('warning')
                        ->schema(function ($record) {
                            $fields = [];
                            foreach (app(ActivityMutation::class)->previewRevert($record) as $key => $change) {
                                $fields[] = Checkbox::make("revert_attributes.{$key}")
                                    ->label($key)
                                    ->disabled($change['conflict'])
                                    ->helperText(__('filament-activity-log::activity.action.revert.helper_text', [
                                        'old' => ActivityLogRedactor::redactValue($key, $change['old']),
                                        'new' => ActivityLogRedactor::redactValue($key, $change['current']),
                                    ]));
                            }

                            return $fields;
                        })
                        ->action(function ($record, array $data) {
                            $attributes = [];
                            foreach ($data['revert_attributes'] ?? [] as $key => $shouldRevert) {
                                if ($shouldRevert) {
                                    $attributes[] = $key;
                                }
                            }

                            if ($attributes === []) {
                                Notification::make()->warning()->title(__('filament-activity-log::activity.action.revert.nothing_selected'))->send();

                                return;
                            }

                            try {
                                app(ActivityMutation::class)->revert($record, $attributes);
                                Notification::make()->success()->title(__('filament-activity-log::activity.action.revert.success'))->send();
                            } catch (InvalidArgumentException|RuntimeException $exception) {
                                Notification::make()->danger()->title($exception->getMessage())->send();
                            }
                        })
                        ->visible(fn ($record) => config('filament-activity-log.table.actions.revert', true) &&
                            ! config('filament-activity-log.privacy.immutable_mode', false) &&
                            $record->event === 'updated' &&
                            app(ActivityMutation::class)->canRevert($record)
                        ),

                    Action::make('restore')
                        ->label(__('filament-activity-log::activity.action.restore.label'))
                        ->icon('heroicon-m-arrow-path')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading(__('filament-activity-log::activity.action.restore.heading'))
                        ->action(function ($record) {
                            try {
                                app(ActivityMutation::class)->restore($record);
                                Notification::make()->success()->title(__('filament-activity-log::activity.action.restore.success'))->send();
                            } catch (InvalidArgumentException|RuntimeException $exception) {
                                Notification::make()->danger()->title($exception->getMessage())->send();
                            }
                        })
                        ->visible(fn ($record) => config('filament-activity-log.table.actions.restore', true) &&
                            ! config('filament-activity-log.privacy.immutable_mode', false) &&
                            $record->event === 'deleted' &&
                            app(ActivityMutation::class)->canRestore($record)
                        ),

                    Action::make('retention_hold')
                        ->label(fn ($record) => __('filament-activity-log::activity.action.retention_hold.'.($record->retention_hold ? 'release' : 'place')))
                        ->icon(fn ($record) => $record->retention_hold ? 'heroicon-m-lock-open' : 'heroicon-m-lock-closed')
                        ->color(fn ($record) => $record->retention_hold ? 'gray' : 'warning')
                        ->requiresConfirmation()
                        ->action(function ($record): void {
                            try {
                                $held = ! (bool) $record->retention_hold;
                                app(RetentionManager::class)->setHold($record, $held);
                                Notification::make()
                                    ->success()
                                    ->title(__('filament-activity-log::activity.action.retention_hold.'.($held ? 'placed' : 'released')))
                                    ->send();
                            } catch (RuntimeException $exception) {
                                Notification::make()->danger()->title($exception->getMessage())->send();
                            }
                        })
                        ->visible(fn ($record) => config('filament-activity-log.table.actions.retention_hold', true)
                            && app(AuditSchema::class)->hasRetentionHold()
                            && Gate::allows('hold', $record)),
                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->modalHeading(__('filament-activity-log::activity.action.delete.heading'))
                        ->modalDescription(__('filament-activity-log::activity.action.delete.confirmation'))
                        ->modalSubmitActionLabel(__('filament-activity-log::activity.action.delete.button'))
                        ->visible(fn ($record) => config('filament-activity-log.table.actions.delete', true) &&
                            config('filament-activity-log.mutations.enabled', false) &&
                            ! config('filament-activity-log.privacy.immutable_mode', false) &&
                            Gate::allows('delete', $record)
                        ),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalDescription(__('filament-activity-log::activity.action.bulk.delete.confirmation'))
                        ->authorizeIndividualRecords('delete')
                        ->visible(fn () => config('filament-activity-log.table.bulk_actions.delete', true) &&
                            config('filament-activity-log.mutations.enabled', false) &&
                            ! config('filament-activity-log.privacy.immutable_mode', false) &&
                            Gate::allows('deleteAny', app(ActivityQuery::class)->modelClass())
                        ),

                    BulkAction::make('place_retention_hold')
                        ->label(__('filament-activity-log::activity.action.bulk.retention_hold.place'))
                        ->icon('heroicon-m-lock-closed')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->authorizeIndividualRecords('hold')
                        ->action(function ($records): void {
                            $count = 0;
                            foreach ($records as $record) {
                                if (app(RetentionManager::class)->setHold($record, true)) {
                                    $count++;
                                }
                            }

                            Notification::make()->success()->title(__('filament-activity-log::activity.action.bulk.retention_hold.placed', ['count' => $count]))->send();
                        })
                        ->visible(fn () => config('filament-activity-log.table.bulk_actions.retention_hold', true)
                            && app(AuditSchema::class)->hasRetentionHold()),

                    BulkAction::make('release_retention_hold')
                        ->label(__('filament-activity-log::activity.action.bulk.retention_hold.release'))
                        ->icon('heroicon-m-lock-open')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->authorizeIndividualRecords('hold')
                        ->action(function ($records): void {
                            $count = 0;
                            foreach ($records as $record) {
                                if (app(RetentionManager::class)->setHold($record, false)) {
                                    $count++;
                                }
                            }

                            Notification::make()->success()->title(__('filament-activity-log::activity.action.bulk.retention_hold.released', ['count' => $count]))->send();
                        })
                        ->visible(fn () => config('filament-activity-log.table.bulk_actions.retention_hold', true)
                            && app(AuditSchema::class)->hasRetentionHold()),

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
                                if ($record->event !== 'deleted' || ! app(ActivityMutation::class)->canRestore($record)) {
                                    continue;
                                }

                                try {
                                    app(ActivityMutation::class)->restore($record);
                                } catch (InvalidArgumentException|RuntimeException) {
                                    continue;
                                }

                                $restoredCount++;
                            }

                            if ($restoredCount > 0) {
                                Notification::make()
                                    ->success()
                                    ->title(__('filament-activity-log::activity.action.bulk.restore.success', ['count' => $restoredCount]))
                                    ->send();
                            }
                        })
                        ->visible(fn () => config('filament-activity-log.table.actions.restore', true) &&
                            config('filament-activity-log.mutations.enabled', false) &&
                            ! config('filament-activity-log.privacy.immutable_mode', false)
                        ),

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
                                if ($record->event !== 'updated' || ! app(ActivityMutation::class)->canRevert($record)) {
                                    continue;
                                }

                                $attributes = collect(app(ActivityMutation::class)->previewRevert($record))
                                    ->reject(fn (array $change): bool => $change['conflict'])
                                    ->keys()
                                    ->all();

                                try {
                                    app(ActivityMutation::class)->revert($record, $attributes);
                                } catch (InvalidArgumentException|RuntimeException) {
                                    continue;
                                }

                                $revertedCount++;
                            }

                            if ($revertedCount > 0) {
                                Notification::make()
                                    ->success()
                                    ->title(__('filament-activity-log::activity.action.bulk.revert.success', ['count' => $revertedCount]))
                                    ->send();
                            }
                        })
                        ->visible(fn () => config('filament-activity-log.table.actions.revert', true) &&
                            config('filament-activity-log.mutations.enabled', false) &&
                            ! config('filament-activity-log.privacy.immutable_mode', false)
                        ),
                ]),
            ]);

        try {
            $plugin = ActivityLogPlugin::get();
        } catch (\Throwable) {
            return $table;
        }

        return $plugin->configureTable($table);
    }
}
