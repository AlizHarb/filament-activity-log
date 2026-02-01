<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Support;

use AlizHarb\ActivityLog\Contracts\HasActivityLogTitle;
use AlizHarb\ActivityLog\Contracts\HasActivityLogUrl;
use Illuminate\Database\Eloquent\Model;

class ActivityLogTitle
{
    public static function get(mixed $model): string
    {
        if (! $model instanceof Model) {
            return (string) ($model ?? '-');
        }

        $title = static::resolveTitle($model);

        // Nested resource support: if the model has a parent defined, prepend it
        if (method_exists($model, 'getActivityLogParent') && ($parent = $model->getActivityLogParent()) instanceof Model) {
            $title = static::resolveTitle($parent).' > '.$title;
        }

        return $title;
    }

    /**
     * Get the URL for the model's activity.
     */
    public static function getUrl(mixed $model): ?string
    {
        if (! $model instanceof Model) {
            return null;
        }

        if ($model instanceof HasActivityLogUrl) {
            return $model->getActivityLogUrl();
        }

        if (method_exists($model, 'getActivityLogUrl')) {
            return $model->getActivityLogUrl();
        }

        return null;
    }

    /**
     * Resolve the base title for a model.
     */
    protected static function resolveTitle(Model $model): string
    {
        if ($model instanceof HasActivityLogTitle) {
            return $model->getActivityLogTitle();
        }

        foreach (['name', 'title', 'email', 'username', 'label'] as $attribute) {
            if ($model->hasAttribute($attribute)) {
                return (string) $model->getAttribute($attribute);
            }
        }

        return class_basename($model).' #'.$model->getKey();
    }
}
