<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Tests\Fixtures;

use Spatie\Activitylog\Models\Activity as SpatieActivity;

class CustomActivityModel extends SpatieActivity
{
    protected $table = 'activity_log';
}
