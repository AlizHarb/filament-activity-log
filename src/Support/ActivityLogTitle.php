<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Support;

use AlizHarb\ActivityLog\Contracts\HasActivityLogTitle;
use Illuminate\Database\Eloquent\Model;

class ActivityLogTitle
{
    public static function get(mixed $model): string
    {
        if (! $model instanceof Model) {
            return (string) ($model ?? '-');
        }

        if ($model instanceof HasActivityLogTitle) {
            return $model->getActivityLogTitle();
        }

        if ($title = $model->getAttribute('name')) {
            return (string) $title;
        }

        if ($title = $model->getAttribute('title')) {
            return (string) $title;
        }

        if ($title = $model->getAttribute('email')) {
            return (string) $title;
        }

        if ($title = $model->getAttribute('username')) {
            return (string) $title;
        }

        return class_basename($model).' #'.$model->getKey();
    }
}
