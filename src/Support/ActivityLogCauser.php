<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Support;

use Filament\Facades\Filament;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLogCauser
{
    /**
     * Resolve the causer model class.
     *
     * In multi-panel applications, it tries to get the model from the current panel's guard provider.
     * Falls back to the default Laravel user model configuration.
     *
     * @return class-string<Model>|null
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
                        /** @var class-string<Model> $model */
                        $model = $provider->getModel();

                        return $model;
                    }
                }
            }
        }

        /** @var class-string<Model>|null $model */
        $model = config('auth.providers.users.model');

        return $model;
    }

    /**
     * Pluck the causer display names and IDs for filter options.
     *
     * @return array<int|string, string>
     */
    public static function pluckOptions(Builder $query): array
    {
        $displayAttribute = config('filament-activity-log.causer.display_attribute', 'name');
        $modelInstance = $query->getModel();
        $keyName = $modelInstance->getKeyName();

        // Check if the display attribute exists as a physical database column
        $schema = $modelInstance->getConnection()->getSchemaBuilder();
        $hasColumn = $schema->hasColumn($modelInstance->getTable(), $displayAttribute);

        if ($hasColumn) {
            // High performance database-level pluck
            return $query->pluck($displayAttribute, $keyName)->toArray();
        }

        // Fallback: If it's a PHP accessor/attribute, load the records and pluck from the hydrated collection
        return $query->get()->pluck($displayAttribute, $keyName)->toArray();
    }
}
