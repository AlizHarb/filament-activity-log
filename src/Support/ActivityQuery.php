<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Support;

use AlizHarb\ActivityLog\ActivityLogPlugin;
use AlizHarb\ActivityLog\Contracts\ScopesActivityQueries;
use AlizHarb\ActivityLog\Exceptions\InvalidConfigurationException;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

/**
 * The single entry point for every activity query issued by the package.
 *
 * Keeping scope application here prevents resources, widgets, exports, and
 * actions from accidentally exposing records outside an application's tenant
 * or security boundary.
 */
class ActivityQuery
{
    /**
     * @return class-string<Activity>
     */
    public function modelClass(): string
    {
        $modelClass = config('activitylog.activity_model') ?? Activity::class;

        if (! is_string($modelClass) || ! is_a($modelClass, Activity::class, true)) {
            throw new InvalidConfigurationException('configuration.activity_model');
        }

        /** @var class-string<Activity> $modelClass */
        return $modelClass;
    }

    /**
     * @return Builder<Activity>
     */
    public function query(): Builder
    {
        $modelClass = $this->modelClass();

        /** @var Builder<Activity> $query */
        $query = $modelClass::query();

        return $this->applyScope($query);
    }

    /**
     * @return Builder<Activity>
     */
    public function withRelations(): Builder
    {
        return $this->query()->with(['causer', 'subject']);
    }

    /**
     * @return Builder<Activity>
     */
    public function forSubject(Model $subject): Builder
    {
        return $this->query()
            ->where('subject_type', $subject->getMorphClass())
            ->where('subject_id', $subject->getKey());
    }

    /**
     * @return Builder<Activity>
     */
    public function forCauser(Model $causer): Builder
    {
        return $this->query()
            ->where('causer_type', $causer->getMorphClass())
            ->where('causer_id', $causer->getKey());
    }

    /**
     * @return Builder<Activity>
     */
    public function forRecord(Model $record): Builder
    {
        return $this->query()->where(function (Builder $query) use ($record): void {
            $query
                ->where(function (Builder $query) use ($record): void {
                    $query->where('subject_type', $record->getMorphClass())
                        ->where('subject_id', $record->getKey());
                })
                ->orWhere(function (Builder $query) use ($record): void {
                    $query->where('causer_type', $record->getMorphClass())
                        ->where('causer_id', $record->getKey());
                });
        });
    }

    public function driverName(): string
    {
        $modelClass = $this->modelClass();

        return (new $modelClass)->getConnection()->getDriverName();
    }

    /**
     * Return a cache namespace only when the configured query boundary can be
     * represented safely. Scoped installations must provide a context key so
     * aggregate results can never leak across tenants or security contexts.
     */
    public function cacheNamespace(): ?string
    {
        $scope = $this->resolveScope();
        $context = 'global';

        if ($scope !== null) {
            $resolver = config('filament-activity-log.cache.context_key');

            if (is_string($resolver) && class_exists($resolver)) {
                $resolver = app($resolver);
            }

            if (! is_callable($resolver)) {
                return null;
            }

            $resolved = app()->call($resolver, [
                'panel' => Filament::getCurrentPanel(),
                'tenant' => Filament::getTenant(),
                'user' => Auth::user(),
            ]);

            if (! is_scalar($resolved) || (string) $resolved === '') {
                return null;
            }

            $context = (string) $resolved;
        }

        $modelClass = $this->modelClass();
        $model = new $modelClass;
        $panel = Filament::getCurrentPanel()?->getId() ?? 'none';
        $identity = implode('|', [
            $modelClass,
            $model->getConnection()->getName(),
            $model->getTable(),
            $panel,
            $context,
        ]);

        return 'filament-activity-log:'.hash('sha256', $identity);
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function applyScope(Builder $query): Builder
    {
        $scope = $this->resolveScope();

        if ($scope === null) {
            return $query;
        }

        if (is_string($scope)) {
            if (! class_exists($scope)) {
                throw new InvalidConfigurationException('configuration.scope_not_found', ['scope' => $scope]);
            }

            $scope = app($scope);
        }

        if ($scope instanceof ScopesActivityQueries) {
            /** @var Builder<TModel> $scopedQuery */
            $scopedQuery = $scope->apply($query);

            return $scopedQuery;
        }

        if (is_callable($scope)) {
            $result = app()->call($scope, [
                'query' => $query,
                'panel' => Filament::getCurrentPanel(),
                'tenant' => Filament::getTenant(),
            ]);

            if ($result === null) {
                return $query;
            }

            if (! $result instanceof Builder) {
                throw new InvalidConfigurationException('configuration.scope_return');
            }

            /** @var Builder<TModel> $result */
            return $result;
        }

        throw new InvalidConfigurationException('configuration.scope_type');
    }

    protected function resolveScope(): Closure|string|ScopesActivityQueries|null
    {
        try {
            $pluginScope = ActivityLogPlugin::get()->getActivityQueryScope();

            if ($pluginScope !== null) {
                return $pluginScope;
            }
        } catch (\Throwable) {
            // The package is also usable outside a booted Filament panel.
        }

        $scope = config('filament-activity-log.query.scope');

        return is_string($scope) || $scope instanceof Closure || $scope instanceof ScopesActivityQueries
            ? $scope
            : null;
    }
}
