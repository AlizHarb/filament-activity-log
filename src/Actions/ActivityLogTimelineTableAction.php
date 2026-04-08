<?php

namespace AlizHarb\ActivityLog\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;

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
     * Whether to show a slim version of the timeline.
     */
    protected bool $isSlim = false;

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

        $this->schema(fn (Schema $schema) => $schema
            ->schema([
                ViewField::make('activities')
                    ->label(__('filament-activity-log::activity.timeline'))
                    ->hiddenLabel()
                    /** @phpstan-ignore-next-line */
                    ->view('filament-activity-log::timeline')
                    ->viewData([
                        'slim' => $this->isSlim(),
                    ])
                    ->dehydrated(false)
                    ->afterStateHydrated(function ($component) {
                        /** @var Model|null $record */
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
    protected function getActivities(?Model $record): Collection
    {
        if (! $record) {
            return collect();
        }

        $with = ['causer', 'subject'];

        // Get activities where the record is the subject
        if ($record instanceof Activity) {
            $subject = $record->subject;
            /** @phpstan-ignore-next-line */
            $activities = $subject ? static::getSubjectActivities($subject, $with) : collect();
        } else {
            $activities = static::getSubjectActivities($record, $with);
        }

        $activities = $activities ?? collect();

        // Also include activities the record caused
        $causalActivities = static::getCausalActivities($record, $with);
        if ($causalActivities->isNotEmpty()) {
            $activities = $activities->merge($causalActivities);
        }

        return $activities->sortByDesc('created_at');
    }

    /**
     * Get subject-side activities using capability detection.
     *
     * Prefers activitiesAsSubject() (v5), then activities() (v4), then raw morphMany.
     */
    protected static function getSubjectActivities(Model $record, array $with): Collection
    {
        if (method_exists($record, 'activitiesAsSubject')) {
            return $record->activitiesAsSubject()->with($with)->latest()->limit(50)->get();
        }

        if (method_exists($record, 'activities')) {
            return $record->activities()->with($with)->latest()->limit(50)->get();
        }

        return $record->morphMany(Activity::class, 'subject')->with($with)->latest()->limit(50)->get();
    }

    /**
     * Get causer-side activities using capability detection.
     *
     * Prefers activitiesAsCauser() (v5), then actions() (v4).
     */
    protected static function getCausalActivities(Model $record, array $with): Collection
    {
        if (method_exists($record, 'activitiesAsCauser')) {
            return $record->activitiesAsCauser()->with($with)->latest()->limit(50)->get();
        }

        if (method_exists($record, 'actions')) {
            return $record->actions()->with($with)->latest()->limit(50)->get();
        }

        return collect();
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
     * Set whether the timeline should be slim.
     */
    public function slim(bool $condition = true): static
    {
        $this->isSlim = $condition;

        return $this;
    }

    /**
     * Check if the timeline is slim.
     */
    public function isSlim(): bool
    {
        return $this->isSlim;
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
