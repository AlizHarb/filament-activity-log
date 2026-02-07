<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Support;

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\EloquentUserProvider;

class ActivityLogCauser
{
    /**
     * Resolve the causer model class.
     *
     * In multi-panel applications, it tries to get the model from the current panel's guard provider.
     * Falls back to the default Laravel user model configuration.
     *
     * @return class-string<\Illuminate\Database\Eloquent\Model>|null
     */
    public static function resolveModelClass(): ?string
    {
        if (class_exists(Filament::class)) {
            $panel = Filament::getCurrentPanel();

            if ($panel) {
                $guardName = $panel->getAuthGuard();
                $guard = Auth::guard($guardName);

                if (method_exists($guard, 'getProvider')) {
                    $provider = $guard->getProvider();

                    if ($provider instanceof EloquentUserProvider) {
                        /** @var class-string<\Illuminate\Database\Eloquent\Model> $model */
                        $model = $provider->getModel();

                        return $model;
                    }
                }
            }
        }

        /** @var class-string<\Illuminate\Database\Eloquent\Model>|null $model */
        $model = config('auth.providers.users.model');

        return $model;
    }
}
