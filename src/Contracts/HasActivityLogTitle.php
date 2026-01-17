<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Contracts;

use Illuminate\Database\Eloquent\Model;

interface HasActivityLogTitle
{
    public function getActivityLogTitle(): string;
}
