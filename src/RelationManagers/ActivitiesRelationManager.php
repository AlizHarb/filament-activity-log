<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\RelationManagers;

use AlizHarb\ActivityLog\Actions\ActivityLogTimelineTableAction;
use AlizHarb\ActivityLog\Resources\ActivityLogs\Schemas\ActivityLogInfolist;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Activities Relation Manager.
 *
 * Displays activity logs as a relation on any resource that has an 'activities' relationship.
 * Provides a table view with event badges, causer information, and timeline actions.
 */
class ActivitiesRelationManager extends RelationManager
{
    /**
     * The name of the relationship.
     */
    protected static string $relationship = 'activities';

    /**
     * The attribute to use for the record title.
     */
    protected static ?string $recordTitleAttribute = 'description';

    /**
     * Configure the infolist schema.
     *
     * Uses the ActivityLogInfolist schema for displaying activity details.
     *
     * @param  Schema  $schema  The schema instance
     * @return Schema The configured schema
     */
    public function infolist(Schema $schema): Schema
    {
        return ActivityLogInfolist::configure($schema);
    }

    /**
     * Configure the relation manager table.
     *
     * Displays activity logs with columns for:
     * - Log name (badge)
     * - Event type (badge with colors)
     * - Causer (user who performed the action)
     * - Description
     * - Created timestamp
     *
     * Includes actions for viewing details and timeline.
     *
     * @param  Table  $table  The table instance
     * @return Table The configured table
     */
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->modifyQueryUsing(function (Builder $query) {
                /** @var \Illuminate\Database\Eloquent\Model $record */
                $record = $this->getOwnerRecord();

                // If the record is a user (or has actions relationship), also include activities they caused
                if (method_exists($record, 'actions')) {
                    $query->orWhere(function (Builder $subQuery) use ($record) {
                        $subQuery->where('causer_id', $record->getKey())
                            ->where('causer_type', $record->getMorphClass());
                    });
                }
            })
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

                TextColumn::make('causer.name')
                    ->label(__('filament-activity-log::activity.table.column.causer'))
                    ->description(fn ($record) => $record->causer?->email)
                    ->searchable(config('filament-activity-log.table.columns.causer.searchable', true))
                    ->sortable(config('filament-activity-log.table.columns.causer.sortable', true))
                    ->visible(config('filament-activity-log.table.columns.causer.visible', true))
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
            ->recordActions([
                ViewAction::make()
                    ->visible(config('filament-activity-log.table.actions.view', true)),
                ActivityLogTimelineTableAction::make()
                    ->visible(config('filament-activity-log.table.actions.timeline', true)),
            ]);
    }
}
