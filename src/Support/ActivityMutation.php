<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Support;

use AlizHarb\ActivityLog\Contracts\RestoresActivitySubjects;
use AlizHarb\ActivityLog\Exceptions\ActivityMutationException;
use AlizHarb\ActivityLog\Exceptions\InvalidConfigurationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Spatie\Activitylog\Models\Activity;
use Throwable;

class ActivityMutation
{
    public function canRevert(Activity $activity): bool
    {
        if (! config('filament-activity-log.mutations.enabled', false) || ! $activity->subject) {
            return false;
        }

        return $this->allowsActivity('update', $activity)
            && $this->allowsSubject('update', $activity->subject)
            && $this->previewRevert($activity) !== [];
    }

    public function canRestore(Activity $activity): bool
    {
        if (! config('filament-activity-log.mutations.enabled', false) || $activity->subject !== null) {
            return false;
        }

        try {
            $modelClass = $this->restorableModelClass($activity);
        } catch (ActivityMutationException) {
            return false;
        }

        return $this->allowsActivity('restore', $activity)
            && $this->allowsSubject('create', $modelClass);
    }

    /**
     * @return array<string, array{old: mixed, audited: mixed, current: mixed, conflict: bool}>
     */
    public function previewRevert(Activity $activity): array
    {
        $subject = $activity->subject;

        if (! $subject) {
            return [];
        }

        $oldValues = $this->safeRevertValues($activity);
        $auditedValues = ActivityChanges::getNewValues($activity);
        $preview = [];

        foreach ($oldValues as $attribute => $oldValue) {
            $auditedValue = data_get($auditedValues, $attribute);
            $currentValue = data_get($subject->getAttributes(), $attribute);

            $preview[$attribute] = [
                'old' => $oldValue,
                'audited' => $auditedValue,
                'current' => $currentValue,
                'conflict' => $currentValue !== $auditedValue,
            ];
        }

        return $preview;
    }

    /**
     * @param  array<int, string>  $attributes
     */
    public function revert(Activity $activity, array $attributes): Model
    {
        Gate::authorize('update', $activity);

        $subject = $activity->subject;

        if (! $subject) {
            throw new ActivityMutationException('mutation.subject_missing');
        }

        $this->authorizeSubject('update', $subject);

        $preview = $this->previewRevert($activity);
        $revertData = [];

        foreach ($attributes as $attribute) {
            if (! array_key_exists($attribute, $preview)) {
                continue;
            }

            if ($preview[$attribute]['conflict'] && config('filament-activity-log.mutations.revert.block_on_conflict', true)) {
                throw new ActivityMutationException('mutation.conflict', ['attribute' => $attribute]);
            }

            $revertData[$attribute] = $preview[$attribute]['old'];
        }

        if ($revertData === []) {
            throw new ActivityMutationException('mutation.no_attributes');
        }

        $subject->getConnection()->transaction(function () use ($subject, $revertData, $activity): void {
            $subject->fill($revertData);
            $subject->save();

            $this->logCompensatingActivity('reverted', $activity, $subject, array_keys($revertData));
        });

        return $subject->refresh();
    }

    public function restore(Activity $activity): Model
    {
        Gate::authorize('restore', $activity);

        $attributes = ActivityChanges::getOldValues($activity);

        if ($attributes === []) {
            $attributes = ActivityChanges::getNewValues($activity);
        }

        if ($attributes === []) {
            throw new ActivityMutationException('mutation.no_snapshot');
        }

        $restorer = $this->restorer();
        $modelClass = $this->restorableModelClass($activity);

        $this->authorizeSubject('create', $modelClass);

        /** @var Model $prototype */
        $prototype = new $modelClass;

        return $prototype->getConnection()->transaction(function () use ($restorer, $activity, $attributes): Model {
            try {
                $model = $restorer->restore($activity, $attributes);
            } catch (Throwable $exception) {
                throw new ActivityMutationException('mutation.restore_failed', previous: $exception);
            }

            $this->logCompensatingActivity('restored_from_audit', $activity, $model, array_keys($attributes));

            return $model;
        });
    }

    /**
     * @return array<string, mixed>
     */
    protected function safeRevertValues(Activity $activity): array
    {
        $values = ActivityChanges::getOldValues($activity);
        $denied = config('filament-activity-log.mutations.revert.denied_attributes', [
            'id',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        if (! config('filament-activity-log.mutations.allow_sensitive_attributes', false)) {
            foreach (array_keys($values) as $attribute) {
                if (ActivityLogRedactor::isSensitiveKey((string) $attribute)) {
                    $denied[] = $attribute;
                }
            }
        }

        return array_diff_key($values, array_flip(array_unique($denied)));
    }

    protected function restorer(): RestoresActivitySubjects
    {
        $restorerClass = config('filament-activity-log.mutations.restore.restorer', DefaultSubjectRestorer::class);
        $restorer = is_string($restorerClass) ? app($restorerClass) : $restorerClass;

        if (! $restorer instanceof RestoresActivitySubjects) {
            throw new InvalidConfigurationException('configuration.restorer');
        }

        return $restorer;
    }

    /**
     * @return class-string<Model>
     */
    protected function restorableModelClass(Activity $activity): string
    {
        $modelClass = Relation::getMorphedModel((string) $activity->subject_type)
            ?? $activity->subject_type;

        if (! is_string($modelClass) || ! is_a($modelClass, Model::class, true)) {
            throw new ActivityMutationException('mutation.restorable_model');
        }

        return $modelClass;
    }

    /**
     * @param  Model|class-string<Model>  $subject
     */
    protected function authorizeSubject(string $ability, Model|string $subject): void
    {
        if (! $this->allowsSubject($ability, $subject)) {
            throw new AuthorizationException;
        }
    }

    /**
     * @param  Model|class-string<Model>  $subject
     */
    protected function allowsSubject(string $ability, Model|string $subject): bool
    {
        if (! config('filament-activity-log.mutations.authorize_subject', true)) {
            return true;
        }

        $actor = Auth::user();

        return $actor instanceof Authenticatable && Gate::forUser($actor)->allows($ability, $subject);
    }

    protected function allowsActivity(string $ability, Activity $activity): bool
    {
        $actor = Auth::user();

        return $actor instanceof Authenticatable && Gate::forUser($actor)->allows($ability, $activity);
    }

    /**
     * @param  array<int, string>  $attributes
     */
    protected function logCompensatingActivity(string $event, Activity $source, Model $subject, array $attributes): void
    {
        if (! config('filament-activity-log.mutations.log_compensating_activity', true)) {
            return;
        }

        $logger = activity((string) config('filament-activity-log.mutations.log_name', 'audit-control'))
            ->performedOn($subject)
            ->event($event)
            ->withProperties([
                'source_activity_id' => $source->getKey(),
                'attributes' => $attributes,
            ]);

        $actor = Auth::user();
        if ($actor instanceof Model) {
            $logger->causedBy($actor);
        }

        $logger->log($event);
    }
}
