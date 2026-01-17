<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ActivityLogEvent: string implements HasLabel, HasColor, HasIcon
{
    case Created = 'created';
    case Updated = 'updated';
    case Deleted = 'deleted';
    case Restored = 'restored';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Created => __('filament-activity-log::activity.event.created'),
            self::Updated => __('filament-activity-log::activity.event.updated'),
            self::Deleted => __('filament-activity-log::activity.event.deleted'),
            self::Restored => __('filament-activity-log::activity.event.restored'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Created => config('filament-activity-log.events.created.color', 'success'),
            self::Updated => config('filament-activity-log.events.updated.color', 'warning'),
            self::Deleted => config('filament-activity-log.events.deleted.color', 'danger'),
            self::Restored => config('filament-activity-log.events.restored.color', 'gray'),
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Created => config('filament-activity-log.events.created.icon', 'heroicon-m-plus'),
            self::Updated => config('filament-activity-log.events.updated.icon', 'heroicon-m-pencil'),
            self::Deleted => config('filament-activity-log.events.deleted.icon', 'heroicon-m-trash'),
            self::Restored => config('filament-activity-log.events.restored.icon', 'heroicon-m-arrow-uturn-left'),
        };
    }
}
