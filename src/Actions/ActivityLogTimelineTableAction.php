<?php

namespace AlizHarb\ActivityLog\Actions;

use Filament\Actions\Action;
use Filament\Support\Colors\Color;

/**
 * Activity Log Timeline Table Action.
 *
 * Displays a beautiful timeline modal showing the complete activity history
 * for a record. Supports customizable icons and colors for different event types.
 */
class ActivityLogTimelineTableAction extends Action
{
    /**
     * Custom icons for different event types.
     *
     * @var array<string, string>
     */
    protected array $icons = [];

    /**
     * Custom colors for different event types.
     *
     * @var array<string, string>
     */
    protected array $colors = [];

    /**
     * Set up the action configuration.
     *
     * Configures the timeline modal with:
     * - Timeline view component
     * - Activity retrieval logic
     * - Modal settings (slide-over, no submit/cancel)
     * - Default icon and color
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->schema(fn (\Filament\Schemas\Schema $schema) => $schema
            ->schema([
                \Filament\Forms\Components\ViewField::make('activities')
                    ->label(__('filament-activity-log::activity.timeline'))
                    ->hiddenLabel()
                    /** @phpstan-ignore-next-line */
                    ->view('filament-activity-log::timeline')
                    ->dehydrated(false)
                    ->afterStateHydrated(function ($component) {
                        /** @var \Illuminate\Database\Eloquent\Model|null $record */
                        $record = $component->getRecord();

                        $component->state($this->getActivities($record));
                    }),
            ]));

        $this->modalHeading(__('filament-activity-log::activity.action.timeline.label'));
        $this->label(__('filament-activity-log::activity.action.timeline.label'));
        $this->color('gray');
        $this->icon('heroicon-m-clock');
        $this->modalSubmitAction(false);
        $this->modalCancelAction(false);
        $this->slideOver();
    }

    /**
     * Retrieve activities for the given record.
     *
     * Fetches activities where the record is the subject or the causer.
     */
    protected function getActivities(?\Illuminate\Database\Eloquent\Model $record): \Illuminate\Support\Collection
    {
        if (! $record) {
            return collect();
        }

        // Get activities where the record is the subject
        if ($record instanceof \Spatie\Activitylog\Models\Activity) {
            $subject = $record->subject;
            /** @phpstan-ignore-next-line */
            $activities = $subject ? $subject->activities()->latest()->get() : collect();
        } elseif (method_exists($record, 'activities')) {
            $activities = $record->activities()->latest()->get();
        } else {
            $activities = $record->morphMany(\Spatie\Activitylog\Models\Activity::class, 'subject')->latest()->get();
        }

        $activities = $activities ?? collect();

        // If the record is a user (or has actions relationship), also include activities they caused
        if (method_exists($record, 'actions')) {
            $actions = $record->actions()->latest()->get();
            $activities = $activities->merge($actions);
        }

        return $activities->sortByDesc('created_at');
    }

    /**
     * Set custom icons for different event types.
     *
     * @param  array<string, string>  $icons  Array of event => icon mappings (e.g., ['created' => 'heroicon-m-plus'])
     */
    public function icons(array $icons): static
    {
        $this->icons = $icons;

        return $this;
    }

    /**
     * Set custom colors for different event types.
     *
     * @param  array<string, string>  $colors  Array of event => color mappings (e.g., ['created' => 'success'])
     */
    public function colors(array $colors): static
    {
        $this->colors = $colors;

        return $this;
    }

    /**
     * Get the configured custom icons.
     *
     * @return array<string, string> Array of event => icon mappings
     */
    public function getIcons(): array
    {
        return $this->icons;
    }

    /**
     * Get the configured custom colors.
     *
     * @return array<string, string> Array of event => color mappings
     */
    public function getColors(): array
    {
        return $this->colors;
    }

    /**
     * Create a new timeline action instance.
     *
     * @param  string|null  $name  The action name (defaults to 'timeline')
     * @return static The action instance
     */
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'timeline');
    }
}
