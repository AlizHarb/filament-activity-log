<?php

namespace AlizHarb\ActivityLog\Resources\ActivityLogs\Pages;

use AlizHarb\ActivityLog\Resources\ActivityLogs\ActivityLogResource;
use Filament\Resources\Pages\ListRecords;

class ListActivityLogs extends ListRecords
{
    protected static string $resource = ActivityLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
