<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Pages;

use AlizHarb\ActivityLog\Resources\ActivityLogs\ActivityLogResource;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

/**
 * User Activities Page.
 *
 * Displays all activities performed by a specific user (causer).
 * Shows a timeline of events like "User logged in", "User created post", etc.
 */
class UserActivitiesPage extends Page implements HasTable
{
    use InteractsWithTable;

    /**
     * The view for the page.
     */
    /**
     * The view for the page.
     */
    protected string $view = 'filament-activity-log::pages.user-activities';

    /**
     * Get the navigation label.
     */
    public static function getNavigationLabel(): string
    {
        return config('filament-activity-log.pages.user_activities.navigation_label')
            ?? __('filament-activity-log::activity.pages.user_activities.title');
    }

    /**
     * Get the navigation group.
     */
    public static function getNavigationGroup(): ?string
    {
        return config('filament-activity-log.pages.user_activities.navigation_group')
            ?? config('filament-activity-log.resource.group');
    }

    /**
     * Get the navigation sort order.
     */
    public static function getNavigationSort(): ?int
    {
        return config('filament-activity-log.pages.user_activities.navigation_sort') ?? 2;
    }

    /**
     * Get the navigation icon.
     */
    public static function getNavigationIcon(): ?string
    {
        return config('filament-activity-log.pages.user_activities.navigation_icon')
            ?? 'heroicon-o-users';
    }

    /**
     * Get the page title.
     */
    public function getTitle(): string
    {
        return __('filament-activity-log::activity.pages.user_activities.title');
    }

    /**
     * Get the page heading.
     */
    public function getHeading(): string
    {
        return __('filament-activity-log::activity.pages.user_activities.heading');
    }

    /**
     * Configure the table.
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Activity::query()
                ->with(['causer', 'subject'])
                ->whereNotNull('causer_id')
                ->latest())
            ->columns([
                TextColumn::make('causer.name')
                    ->label(__('filament-activity-log::activity.table.column.causer'))
                    ->searchable()
                    ->sortable()
                    ->default('-'),

                TextColumn::make('event')
                    ->label(__('filament-activity-log::activity.table.column.event'))
                    ->badge()
                    ->colors([
                        'success' => 'created',
                        'warning' => 'updated',
                        'danger' => 'deleted',
                        'gray' => 'restored',
                    ])
                    ->searchable()
                    ->sortable(),

                TextColumn::make('subject_type')
                    ->label(__('filament-activity-log::activity.table.column.subject'))
                    ->formatStateUsing(fn ($state) => $state ? class_basename($state) : '-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('description')
                    ->label(__('filament-activity-log::activity.table.column.description'))
                    ->limit(50)
                    ->searchable()
                    ->default('-')
                    ->toggleable(),

                TextColumn::make('properties.ip_address')
                    ->label(__('filament-activity-log::activity.table.column.ip_address'))
                    ->searchable()
                    ->toggleable()
                    ->default('-'),

                TextColumn::make('created_at')
                    ->label(__('filament-activity-log::activity.table.column.created_at'))
                    ->dateTime(config('filament-activity-log.datetime_format', 'M d, Y H:i:s'))
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('causer_id')
                    ->label(__('filament-activity-log::activity.filter.causer'))
                    ->options(function () {
                        $model = config('auth.providers.users.model');

                        if (! class_exists($model)) {
                            return [];
                        }

                        return $model::query()->pluck('name', 'id')->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->default(Auth::id()),

                SelectFilter::make('event')
                    ->label(__('filament-activity-log::activity.filter.event'))
                    ->options([
                        'created' => __('filament-activity-log::activity.event.created'),
                        'updated' => __('filament-activity-log::activity.event.updated'),
                        'deleted' => __('filament-activity-log::activity.event.deleted'),
                        'restored' => __('filament-activity-log::activity.event.restored'),
                    ])
                    ->multiple(),

                SelectFilter::make('subject_type')
                    ->label(__('filament-activity-log::activity.filter.subject_type'))
                    ->options(function () {
                        return Activity::query()
                            ->whereNotNull('subject_type')
                            ->distinct()
                            ->pluck('subject_type')
                            ->mapWithKeys(fn ($type) => [$type => class_basename($type)])
                            ->toArray();
                    })
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll(config('filament-activity-log.pages.user_activities.polling_interval'))
            ->striped();
    }

    /**
     * Determine if the page should be registered in navigation.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return config('filament-activity-log.pages.user_activities.enabled', true);
    }

    /**
     * Check if the user can access this page.
     */
    public static function canAccess(): bool
    {
        // Use the same authorization as the main resource
        return ActivityLogResource::canViewAny();
    }
}
