<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Spatie\Activitylog\Contracts\Activity;

/**
 * Activity Policy.
 *
 * Authorizes actions on the Activity model based on configuration.
 * When permissions are disabled in config, viewAny and view return true by default.
 * All other actions (create, update, delete, restore, forceDelete) return false by default.
 */
class ActivityPolicy
{
    use HandlesAuthorization;

    /**
     * Check for custom authorization.
     *
     * @param  Authenticatable  $user  The authenticated user
     * @return bool|null Boolean result if custom auth handles it, null otherwise
     */
    protected function checkCustomAuthorization(Authenticatable $user): ?bool
    {
        $customAuthorization = config('filament-activity-log.permissions.custom_authorization');

        if (is_callable($customAuthorization)) {
            return $customAuthorization($user);
        }

        if (is_string($customAuthorization) && class_exists($customAuthorization)) {
            $instance = app($customAuthorization);

            if (is_callable($instance)) {
                return $instance($user);
            }
        }

        return null;
    }

    protected function checkMutationAuthorization(Authenticatable $user, string $ability, ?Activity $activity = null): ?bool
    {
        $authorizer = config('filament-activity-log.mutations.custom_authorization');

        if (is_string($authorizer) && class_exists($authorizer)) {
            $authorizer = app($authorizer);
        }

        if (! is_callable($authorizer)) {
            return null;
        }

        return (bool) app()->call($authorizer, [
            'user' => $user,
            'ability' => $ability,
            'activity' => $activity,
        ]);
    }

    protected function mutationAllowed(Authenticatable $user, string $ability, ?Activity $activity = null): bool
    {
        if (! config('filament-activity-log.mutations.enabled', false)) {
            return false;
        }

        $customResult = $this->checkMutationAuthorization($user, $ability, $activity);
        if ($customResult !== null) {
            return $customResult;
        }

        if (! config('filament-activity-log.permissions.enabled', false)) {
            return false;
        }

        $permission = config("filament-activity-log.permissions.{$ability}");

        return is_string($permission) && $permission !== '' && Gate::forUser($user)->allows($permission);
    }

    /**
     * Determine whether the user can view any activities.
     *
     * Returns true by default when permissions are disabled.
     * When enabled, checks the configured 'view_any' permission.
     *
     * @param  Authenticatable  $user  The authenticated user
     * @return bool True if the user can view any activities
     */
    public function viewAny(Authenticatable $user): bool
    {
        // Check for custom authorization callback first
        $result = $this->checkCustomAuthorization($user);
        if ($result !== null) {
            return $result;
        }

        if (! config('filament-activity-log.permissions.enabled', false)) {
            return true;
        }

        $permission = config('filament-activity-log.permissions.view_any');

        return $permission ? Gate::forUser($user)->allows($permission) : true;
    }

    /**
     * Determine whether the user can view the activity.
     *
     * Returns true by default when permissions are disabled.
     * When enabled, checks the configured 'view' permission.
     *
     * @param  Authenticatable  $user  The authenticated user
     * @param  Activity  $activity  The activity model instance
     * @return bool True if the user can view the activity
     */
    public function view(Authenticatable $user, Activity $activity): bool
    {
        // Check for custom authorization callback first
        $result = $this->checkCustomAuthorization($user);
        if ($result !== null) {
            return $result;
        }

        if (! config('filament-activity-log.permissions.enabled', false)) {
            return true;
        }

        $permission = config('filament-activity-log.permissions.view');

        return $permission ? Gate::forUser($user)->allows($permission) : true;
    }

    /**
     * Determine whether the user can create activities.
     *
     * Returns false by default as activities are typically auto-generated.
     * When permissions are enabled, checks the configured 'create' permission.
     *
     * @param  Authenticatable  $user  The authenticated user
     * @return bool True if the user can create activities
     */
    public function create(Authenticatable $user): bool
    {
        // Check for custom authorization callback first
        $result = $this->checkCustomAuthorization($user);
        if ($result !== null) {
            return $result;
        }

        if (! config('filament-activity-log.permissions.enabled', false)) {
            return false;
        }

        $permission = config('filament-activity-log.permissions.create');

        return $permission ? Gate::forUser($user)->allows($permission) : false;
    }

    /**
     * Determine whether the user can update the activity.
     *
     * Returns false by default as activities should not be modified.
     * When permissions are enabled, checks the configured 'update' permission.
     *
     * @param  Authenticatable  $user  The authenticated user
     * @param  Activity  $activity  The activity model instance
     * @return bool True if the user can update the activity
     */
    public function update(Authenticatable $user, Activity $activity): bool
    {
        return $this->mutationAllowed($user, 'update', $activity);
    }

    /**
     * Determine whether the user can delete the activity.
     *
     * Returns false by default when permissions are disabled.
     * When enabled, checks the configured 'delete' permission.
     *
     * @param  Authenticatable  $user  The authenticated user
     * @param  Activity  $activity  The activity model instance
     * @return bool True if the user can delete the activity
     */
    public function delete(Authenticatable $user, Activity $activity): bool
    {
        if ($this->isRetentionHeld($activity)) {
            return false;
        }

        return $this->mutationAllowed($user, 'delete', $activity);
    }

    /**
     * Determine whether the user can restore the activity.
     *
     * Returns false by default when permissions are disabled.
     * When enabled, checks the configured 'restore' permission.
     *
     * @param  Authenticatable  $user  The authenticated user
     * @param  Activity  $activity  The activity model instance
     * @return bool True if the user can restore the activity
     */
    public function restore(Authenticatable $user, Activity $activity): bool
    {
        return $this->mutationAllowed($user, 'restore', $activity);
    }

    /**
     * Determine whether the user can permanently delete the activity.
     *
     * Returns false by default when permissions are disabled.
     * When enabled, checks the configured 'force_delete' permission.
     *
     * @param  Authenticatable  $user  The authenticated user
     * @param  Activity  $activity  The activity model instance
     * @return bool True if the user can force delete the activity
     */
    public function forceDelete(Authenticatable $user, Activity $activity): bool
    {
        if ($this->isRetentionHeld($activity)) {
            return false;
        }

        return $this->mutationAllowed($user, 'force_delete', $activity);
    }

    public function deleteAny(Authenticatable $user): bool
    {
        return $this->mutationAllowed($user, 'delete_any');
    }

    public function pruneAny(Authenticatable $user): bool
    {
        return $this->mutationAllowed($user, 'prune');
    }

    public function exportAny(Authenticatable $user): bool
    {
        if (! config('filament-activity-log.permissions.enabled', false)) {
            return config('filament-activity-log.permissions.allow_export_when_disabled', false);
        }

        $permission = config('filament-activity-log.permissions.export');

        return is_string($permission) && $permission !== '' && Gate::forUser($user)->allows($permission);
    }

    public function hold(Authenticatable $user, Activity $activity): bool
    {
        if (! config('filament-activity-log.retention.enabled', true)) {
            return false;
        }

        $customResult = $this->checkMutationAuthorization($user, 'hold', $activity);
        if ($customResult !== null) {
            return $customResult;
        }

        if (! config('filament-activity-log.permissions.enabled', false)) {
            return false;
        }

        $permission = config('filament-activity-log.permissions.hold');

        return is_string($permission) && $permission !== '' && Gate::forUser($user)->allows($permission);
    }

    protected function isRetentionHeld(Activity $activity): bool
    {
        return $activity instanceof Model && (bool) $activity->getAttribute('retention_hold');
    }
}
