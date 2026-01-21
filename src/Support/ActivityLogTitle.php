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

        if ($model->hasAttribute('name')) {
            return (string) $model->getAttribute('name');
        }

        if ($model->hasAttribute('title')) {
            return (string) $model->getAttribute('title');
        }

        if ($model->hasAttribute('email')) {
            return (string) $model->getAttribute('email');
        }

        if ($model->hasAttribute('username')) {
            return (string) $model->getAttribute('username');
        }

        return class_basename($model).' #'.$model->getKey();
    }
}
