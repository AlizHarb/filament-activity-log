<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog;

use AlizHarb\ActivityLog\Resources\ActivityLogs\ActivityLogResource;
use Closure;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Concerns\EvaluatesClosures;
use UnitEnum;

/**
 * Activity Log Plugin for FilamentPHP.
 *
 * Provides comprehensive activity logging functionality including:
 * - Activity log resource with timeline views
 * - Dashboard widgets for activity monitoring
 * - Customizable navigation and labels
 * - Role-based access control
 */
class ActivityLogPlugin implements Plugin
{
    use EvaluatesClosures;

    /**
     * The ID of the plugin.
     */
    public static string $name = 'filament-activity-log';

    /**
     * The label for the activity log resource.
     */
    protected string|Closure|null $label = null;

    /**
     * The plural label for the activity log resource.
     */
    protected string|Closure|null $pluralLabel = null;

    /**
     * The navigation group for the activity log resource.
     */
    protected string|Closure|null|UnitEnum $navigationGroup = null;

    /**
     * The navigation icon for the activity log resource.
     */
    protected string|Closure|null $navigationIcon = null;

    /**
     * The navigation sort order for the activity log resource.
     */
    protected int|Closure|null $navigationSort = null;

    /**
     * The navigation count badge for the activity log resource.
     */
    protected string|Closure|null $navigationCountBadge = null;

    /**
     * Whether resource actions should be hidden.
     */
    protected bool|Closure|null $isResourceActionHidden = null;

    /**
     * Whether restore actions should be hidden.
     */
    protected bool|Closure|null $isRestoreActionHidden = null;

    /**
     * Get the ID of the plugin.
     */
    public function getId(): string
    {
        return static::$name;
    }

    /**
     * Register the plugin with the panel.
     */
    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                config('filament-activity-log.resource.class', ActivityLogResource::class),
            ])
            ->widgets($this->getWidgets());
    }

    /**
     * Get the widgets to register with the panel.
     *
     * Returns an array of widget class names based on configuration.
     * Returns empty array if widgets are disabled in config.
     *
     * @return array<class-string>
     */
    public function getWidgets(): array
    {
        if (! config('filament-activity-log.widgets.enabled', true)) {
            return [];
        }

        return config('filament-activity-log.widgets.widgets', []);
    }

    /**
     * Boot the plugin.
     *
     * Called after the plugin has been registered.
     *
     * @param  Panel  $panel  The Filament panel instance
     */
    public function boot(Panel $panel): void {}

    /**
     * Create a new instance of the plugin.
     */
    public static function make(): static
    {
        return app(static::class);
    }

    /**
     * Get the plugin instance from the Filament panel.
     *
     * @return static The plugin instance
     */
    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    /**
     * Set the label for the activity log resource.
     *
     * @param  string|Closure|null  $label  The label or a closure that returns the label
     */
    public function label(string|Closure|null $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get the evaluated label for the activity log resource.
     *
     * @return string The evaluated label or default translation
     */
    public function getLabel(): string
    {
        return $this->evaluate($this->label) ?? __('filament-activity-log::activity.label');
    }

    /**
     * Set the plural label for the activity log resource.
     *
     * @param  string|Closure|null  $label  The plural label or a closure that returns it
     */
    public function pluralLabel(string|Closure|null $label): static
    {
        $this->pluralLabel = $label;

        return $this;
    }

    /**
     * Get the evaluated plural label for the activity log resource.
     *
     * @return string The evaluated plural label or default translation
     */
    public function getPluralLabel(): string
    {
        return $this->evaluate($this->pluralLabel) ?? __('filament-activity-log::activity.plural_label');
    }

    /**
     * Set the navigation group for the activity log resource.
     *
     * @param  string|Closure|null  $group  The navigation group name or a closure
     */
    public function navigationGroup(string|Closure|null|UnitEnum $group): static
    {
        $this->navigationGroup = $group;

        return $this;
    }

    /**
     * Get the evaluated navigation group.
     *
     * @return string|null The evaluated navigation group or config value
     */
    public function getNavigationGroup(): UnitEnum|string|null
    {
        return $this->evaluate($this->navigationGroup) ?? config('filament-activity-log.resource.group');
    }

    /**
     * Set the navigation icon for the activity log resource.
     *
     * @param  string|Closure|null  $icon  The Heroicon name or a closure
     */
    public function navigationIcon(string|Closure|null $icon): static
    {
        $this->navigationIcon = $icon;

        return $this;
    }

    /**
     * Get the evaluated navigation icon.
     *
     * @return string|null The evaluated icon name or config value
     */
    public function getNavigationIcon(): ?string
    {
        return $this->evaluate($this->navigationIcon) ?? config('filament-activity-log.resource.navigation_icon');
    }

    /**
     * Set the navigation sort order for the activity log resource.
     *
     * @param  int|Closure|null  $sort  The sort order or a closure
     */
    public function navigationSort(int|Closure|null $sort): static
    {
        $this->navigationSort = $sort;

        return $this;
    }

    /**
     * Get the evaluated navigation sort order.
     *
     * @return int|null The evaluated sort order or config value
     */
    public function getNavigationSort(): ?int
    {
        return $this->evaluate($this->navigationSort) ?? config('filament-activity-log.resource.sort');
    }

    /**
     * Set the navigation count badge for the activity log resource.
     *
     * @param  string|Closure|null  $badge  The badge value or a closure
     */
    public function navigationCountBadge(string|Closure|null $badge): static
    {
        $this->navigationCountBadge = $badge;

        return $this;
    }

    /**
     * Get the evaluated navigation count badge.
     *
     * @return string|null The evaluated badge value
     */
    public function getNavigationCountBadge(): ?string
    {
        return $this->evaluate($this->navigationCountBadge);
    }
}
