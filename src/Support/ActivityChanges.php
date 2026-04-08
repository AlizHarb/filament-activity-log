<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Support;

use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;

class ActivityChanges
{
    /**
     * Get old values from the activity, preferring attribute_changes over properties.
     *
     * @return array<string, mixed>
     */
    public static function getOldValues(Activity $activity): array
    {
        $attributeChanges = static::getAttributeChanges($activity);

        if (isset($attributeChanges['old']) && is_array($attributeChanges['old'])) {
            return $attributeChanges['old'];
        }

        return (array) ($activity->properties['old'] ?? []);
    }

    /**
     * Get new values from the activity, preferring attribute_changes over properties.
     *
     * @return array<string, mixed>
     */
    public static function getNewValues(Activity $activity): array
    {
        $attributeChanges = static::getAttributeChanges($activity);

        if (isset($attributeChanges['attributes']) && is_array($attributeChanges['attributes'])) {
            return $attributeChanges['attributes'];
        }

        return (array) ($activity->properties['attributes'] ?? []);
    }

    /**
     * Check if the activity has any tracked changes (old or new values).
     */
    public static function hasChanges(Activity $activity): bool
    {
        return static::hasOldValues($activity) || static::hasNewValues($activity);
    }

    /**
     * Check if the activity has old values.
     */
    public static function hasOldValues(Activity $activity): bool
    {
        return ! empty(static::getOldValues($activity));
    }

    /**
     * Check if the activity has new values.
     */
    public static function hasNewValues(Activity $activity): bool
    {
        return ! empty(static::getNewValues($activity));
    }

    /**
     * Normalize attribute_changes whether it arrives as an array, collection, or JSON string.
     *
     * @return array<string, mixed>
     */
    public static function getAttributeChanges(Activity $activity): array
    {
        if (! isset($activity->attribute_changes)) {
            return [];
        }

        $changes = $activity->attribute_changes;

        if ($changes instanceof Collection) {
            return $changes->toArray();
        }

        if (is_string($changes)) {
            $decoded = json_decode($changes, true);

            return is_array($decoded) ? $decoded : [];
        }

        if (is_array($changes)) {
            return $changes;
        }

        return [];
    }
}
