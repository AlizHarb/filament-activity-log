<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User;
use Spatie\Activitylog\Models\Activity;

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
     * @param  User  $user  The authenticated user
     * @return bool|null Boolean result if custom auth handles it, null otherwise
     */
    protected function checkCustomAuthorization(User $user): ?bool
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

    /**
     * Determine whether the user can view any activities.
     *
     * Returns true by default when permissions are disabled.
     * When enabled, checks the configured 'view_any' permission.
     *
     * @param  User  $user  The authenticated user
     * @return bool True if the user can view any activities
     */
    public function viewAny(User $user): bool
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

        return $permission ? $user->can($permission) : true;
    }

    /**
     * Determine whether the user can view the activity.
     *
     * Returns true by default when permissions are disabled.
     * When enabled, checks the configured 'view' permission.
     *
     * @param  User  $user  The authenticated user
     * @param  Activity  $activity  The activity model instance
     * @return bool True if the user can view the activity
     */
    public function view(User $user, Activity $activity): bool
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

        return $permission ? $user->can($permission) : true;
    }

    /**
     * Determine whether the user can create activities.
     *
     * Returns false by default as activities are typically auto-generated.
     * When permissions are enabled, checks the configured 'create' permission.
     *
     * @param  User  $user  The authenticated user
     * @return bool True if the user can create activities
     */
    public function create(User $user): bool
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

        return $permission ? $user->can($permission) : false;
    }

    /**
     * Determine whether the user can update the activity.
     *
     * Returns false by default as activities should not be modified.
     * When permissions are enabled, checks the configured 'update' permission.
     *
     * @param  User  $user  The authenticated user
     * @param  Activity  $activity  The activity model instance
     * @return bool True if the user can update the activity
     */
    public function update(User $user, Activity $activity): bool
    {
        // Check for custom authorization callback first
        $result = $this->checkCustomAuthorization($user);
        if ($result !== null) {
            return $result;
        }

        if (! config('filament-activity-log.permissions.enabled', false)) {
            return false;
        }

        $permission = config('filament-activity-log.permissions.update');

        return $permission ? $user->can($permission) : false;
    }

    /**
     * Determine whether the user can delete the activity.
     *
     * Returns false by default when permissions are disabled.
     * When enabled, checks the configured 'delete' permission.
     *
     * @param  User  $user  The authenticated user
     * @param  Activity  $activity  The activity model instance
     * @return bool True if the user can delete the activity
     */
    public function delete(User $user, Activity $activity): bool
    {
        // Check for custom authorization callback first
        $result = $this->checkCustomAuthorization($user);
        if ($result !== null) {
            return $result;
        }

        if (! config('filament-activity-log.permissions.enabled', false)) {
            return false;
        }

        $permission = config('filament-activity-log.permissions.delete');

        return $permission ? $user->can($permission) : false;
    }

    /**
     * Determine whether the user can restore the activity.
     *
     * Returns false by default when permissions are disabled.
     * When enabled, checks the configured 'restore' permission.
     *
     * @param  User  $user  The authenticated user
     * @param  Activity  $activity  The activity model instance
     * @return bool True if the user can restore the activity
     */
    public function restore(User $user, Activity $activity): bool
    {
        // Check for custom authorization callback first
        $result = $this->checkCustomAuthorization($user);
        if ($result !== null) {
            return $result;
        }

        if (! config('filament-activity-log.permissions.enabled', false)) {
            return false;
        }

        $permission = config('filament-activity-log.permissions.restore');

        return $permission ? $user->can($permission) : false;
    }

    /**
     * Determine whether the user can permanently delete the activity.
     *
     * Returns false by default when permissions are disabled.
     * When enabled, checks the configured 'force_delete' permission.
     *
     * @param  User  $user  The authenticated user
     * @param  Activity  $activity  The activity model instance
     * @return bool True if the user can force delete the activity
     */
    public function forceDelete(User $user, Activity $activity): bool
    {
        // Check for custom authorization callback first
        $result = $this->checkCustomAuthorization($user);
        if ($result !== null) {
            return $result;
        }

        if (! config('filament-activity-log.permissions.enabled', false)) {
            return false;
        }

        $permission = config('filament-activity-log.permissions.force_delete');

        return $permission ? $user->can($permission) : false;
    }
}
