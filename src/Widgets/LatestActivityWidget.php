<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Spatie\Activitylog\Models\Activity;

/**
 * Latest Activity Widget.
 *
 * Displays a table of the most recent activities with configurable columns.
 * Shows event type, causer, subject, description, and timestamp.
 */
class LatestActivityWidget extends BaseWidget
{
    /**
     * The sort order for the widget.
     */
    protected static ?int $sort = null;

    /**
     * The column span for the widget.
     */
    protected array|string|int $columnSpan = 'full';

    /**
     * Get the widget heading.
     *
     * @return string|null The heading text from config or translation
     */
    public function getHeading(): ?string
    {
        return config('filament-activity-log.widgets.latest_activity.heading')
            ?? __('filament-activity-log::activity.widgets.latest_activity');
    }

    /**
     * Get the sort order for the widget.
     *
     * @return int The sort order from config or default
     */
    public static function getSort(): int
    {
        return config('filament-activity-log.widgets.latest_activity.sort', 2);
    }

    /**
     * Get the polling interval for auto-refresh.
     *
     * @return string|null The polling interval (e.g., '10s', '1m') or null to disable
     */
    protected function getPollingInterval(): ?string
    {
        return config('filament-activity-log.widgets.latest_activity.polling_interval');
    }

    /**
     * Determine if the widget can be viewed.
     *
     * Checks if widgets are enabled globally and if this specific widget is enabled.
     *
     * @return bool True if the widget should be displayed
     */
    public static function canView(): bool
    {
        if (! config('filament-activity-log.widgets.enabled', true)) {
            return false;
        }

        if (! config('filament-activity-log.widgets.latest_activity.enabled', true)) {
            return false;
        }

        return true;
    }

    /**
     * Configure the widget table.
     *
     * Builds the table with configurable columns based on config settings.
     * Columns include: event, causer, subject type, description, and created_at.
     *
     * @param  Table  $table  The table instance
     * @return Table The configured table
     */
    public function table(Table $table): Table
    {
        $columns = [];

        if (config('filament-activity-log.widgets.latest_activity.columns.event', true)) {
            $columns[] = TextColumn::make('event')
                ->label(__('filament-activity-log::activity.table.column.event'))
                ->badge()
                ->colors([
                    'success' => 'created',
                    'warning' => 'updated',
                    'danger' => 'deleted',
                    'gray' => 'restored',
                ]);
        }

        if (config('filament-activity-log.widgets.latest_activity.columns.causer', true)) {
            $columns[] = TextColumn::make('causer.name')
                ->label(__('filament-activity-log::activity.table.column.causer'))
                ->default('-')
                ->limit(config('filament-activity-log.widgets.latest_activity.columns.causer_limit', 30));
        }

        if (config('filament-activity-log.widgets.latest_activity.columns.subject_type', true)) {
            $columns[] = TextColumn::make('subject_type')
                ->label(__('filament-activity-log::activity.table.column.subject'))
                ->formatStateUsing(fn ($state) => $state ? class_basename($state) : '-')
                ->limit(config('filament-activity-log.widgets.latest_activity.columns.subject_type_limit', 30));
        }

        if (config('filament-activity-log.widgets.latest_activity.columns.description', true)) {
            $columns[] = TextColumn::make('description')
                ->label(__('filament-activity-log::activity.table.column.description'))
                ->limit(config('filament-activity-log.widgets.latest_activity.columns.description_limit', 50))
                ->default('-');
        }

        if (config('filament-activity-log.widgets.latest_activity.columns.created_at', true)) {
            $columns[] = TextColumn::make('created_at')
                ->label(__('filament-activity-log::activity.table.column.created_at'))
                ->dateTime(config('filament-activity-log.datetime_format', 'M d, Y H:i:s'))
                ->since()
                ->sortable();
        }

        return $table
            ->query(
                Activity::query()
                    ->with(['causer', 'subject'])
                    ->latest()
                    ->limit(config('filament-activity-log.widgets.latest_activity.limit', 10))
            )
            ->heading($this->getHeading())
            ->columns($columns)
            ->paginated(config('filament-activity-log.widgets.latest_activity.paginated', false));
    }
}
