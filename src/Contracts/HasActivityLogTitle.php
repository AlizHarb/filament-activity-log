<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Contracts;

interface HasActivityLogTitle
{
    public function getActivityLogTitle(): string;
}
