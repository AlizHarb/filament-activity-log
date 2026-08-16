<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Contracts;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;

interface RestoresActivitySubjects
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function restore(Activity $activity, array $attributes): Model;
}
