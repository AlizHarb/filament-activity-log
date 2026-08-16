<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Support;

use AlizHarb\ActivityLog\Contracts\RestoresActivitySubjects;
use AlizHarb\ActivityLog\Exceptions\ActivityMutationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Spatie\Activitylog\Models\Activity;

class DefaultSubjectRestorer implements RestoresActivitySubjects
{
    public function restore(Activity $activity, array $attributes): Model
    {
        $modelClass = Relation::getMorphedModel((string) $activity->subject_type) ?? $activity->subject_type;

        if (! is_string($modelClass) || ! is_a($modelClass, Model::class, true)) {
            throw new ActivityMutationException('mutation.restorable_model');
        }

        /** @var Model $model */
        $model = new $modelClass;
        $attributes = $this->allowedAttributes($model, $attributes);

        if ($attributes === []) {
            throw new ActivityMutationException('mutation.no_safe_attributes');
        }

        $model->fill($attributes);
        $model->save();

        return $model;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    protected function allowedAttributes(Model $model, array $attributes): array
    {
        $allow = config('filament-activity-log.mutations.restore.allowed_attributes');

        if (is_array($allow)) {
            $attributes = array_intersect_key($attributes, array_flip($allow));
        }

        $denied = config('filament-activity-log.mutations.restore.denied_attributes', [
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        if (! config('filament-activity-log.mutations.restore.preserve_primary_key', false)) {
            $denied[] = $model->getKeyName();
        }

        if (! config('filament-activity-log.mutations.allow_sensitive_attributes', false)) {
            foreach (array_keys($attributes) as $attribute) {
                if (ActivityLogRedactor::isSensitiveKey((string) $attribute)) {
                    $denied[] = $attribute;
                }
            }
        }

        return array_diff_key($attributes, array_flip(array_unique($denied)));
    }
}
