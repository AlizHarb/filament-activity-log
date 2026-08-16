<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface CollectsActivityContext
{
    /**
     * @return array<string, mixed>
     */
    public function collect(Request $request, ?Model $subject = null, ?Model $causer = null): array;
}
