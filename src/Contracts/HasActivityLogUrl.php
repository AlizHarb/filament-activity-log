<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Contracts;

interface HasActivityLogUrl
{
    public function getActivityLogUrl(): ?string;
}
