<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Support;

class ActivityEvents
{
    /**
     * Original color and icon for the built-in events, used when a config
     * entry does not set them.
     *
     * @var array<string, array{color: string, icon: string}>
     */
    private const FALLBACKS = [
        'created' => [
            'color' => 'success',
            'icon' => 'heroicon-m-plus',
        ],
        'updated' => [
            'color' => 'warning',
            'icon' => 'heroicon-m-pencil',
        ],
        'deleted' => [
            'color' => 'danger',
            'icon' => 'heroicon-m-trash',
        ],
        'restored' => [
            'color' => 'gray',
            'icon' => 'heroicon-m-arrow-uturn-left',
        ],
    ];

    /**
     * @return array<string, array<array-key, mixed>>
     */
    public static function definitions(): array
    {
        $events = config('filament-activity-log.events', []);

        if (! is_array($events)) {
            return [];
        }

        $definitions = [];

        foreach ($events as $name => $definition) {
            if (! is_string($name) || $name === '' || ! is_array($definition)) {
                continue;
            }

            $definitions[$name] = $definition;
        }

        return $definitions;
    }

    /**
     * Filter options for the configured events.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (array_keys(static::definitions()) as $event) {
            $options[$event] = static::label($event);
        }

        return $options;
    }

    public static function label(mixed $event): string
    {
        if (! is_string($event) || $event === '') {
            return '';
        }

        $configured = static::definitions()[$event]['label'] ?? null;

        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        $key = "filament-activity-log::activity.event.{$event}";
        $translated = __($key);

        if (is_string($translated) && $translated !== '' && $translated !== $key) {
            return $translated;
        }

        return ucfirst($event);
    }

    /**
     * @return string|array<array-key, mixed>|null
     */
    public static function color(mixed $event): string|array|null
    {
        if (! is_string($event) || $event === '') {
            return 'gray';
        }

        $definition = static::definitions()[$event] ?? [];
        $color = $definition['color'] ?? (self::FALLBACKS[$event]['color'] ?? 'gray');

        if (is_string($color) || is_array($color)) {
            return $color;
        }

        return 'gray';
    }

    public static function icon(mixed $event): ?string
    {
        if (! is_string($event) || $event === '') {
            return null;
        }

        $definition = static::definitions()[$event] ?? [];
        $icon = $definition['icon'] ?? (self::FALLBACKS[$event]['icon'] ?? null);

        return is_string($icon) && $icon !== '' ? $icon : null;
    }
}
