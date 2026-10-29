<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Enums;

use AlizHarb\ActivityLog\Support\ActivityEvents;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ActivityLogEvent: string implements HasColor, HasIcon, HasLabel
{
    case Created = 'created';
    case Updated = 'updated';
    case Deleted = 'deleted';
    case Restored = 'restored';

    public function getLabel(): string
    {
        return ActivityEvents::label($this->value);
    }

    public function getColor(): string|array|null
    {
        return ActivityEvents::color($this->value);
    }

    public function getIcon(): string
    {
        return ActivityEvents::icon($this->value) ?? match ($this) {
            self::Created => 'heroicon-m-plus',
            self::Updated => 'heroicon-m-pencil',
            self::Deleted => 'heroicon-m-trash',
            self::Restored => 'heroicon-m-arrow-uturn-left',
        };
    }
}
