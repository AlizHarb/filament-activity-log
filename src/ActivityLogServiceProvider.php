<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog;

use AlizHarb\ActivityLog\Policies\ActivityPolicy;
use AlizHarb\ActivityLog\Support\ActivityCache;
use AlizHarb\ActivityLog\Support\ActivityMutation;
use AlizHarb\ActivityLog\Support\ActivityPruner;
use AlizHarb\ActivityLog\Support\ActivityQuery;
use AlizHarb\ActivityLog\Support\AuditMetadata;
use AlizHarb\ActivityLog\Support\AuditRuleEngine;
use AlizHarb\ActivityLog\Support\AuditSchema;
use AlizHarb\ActivityLog\Support\RetentionManager;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

/**
 * Class ActivityLogServiceProvider
 *
 * The service provider for the Activity Log package.
 * Handles package configuration, asset registration, and policy registration.
 */
class ActivityLogServiceProvider extends PackageServiceProvider
{
    /**
     * The name of the package.
     */
    public static string $name = 'filament-activity-log';

    /**
     * The namespace for views.
     */
    public static string $viewNamespace = 'filament-activity-log';

    /**
     * Configure the package.
     */
    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasConfigFile()
            ->hasTranslations()
            ->hasViews(static::$viewNamespace)
            ->hasMigration('upgrade_activity_log_with_audit_control_columns')
            ->hasCommands([
                Commands\InstallCommand::class,
                Commands\BackfillAuditMetadataCommand::class,
                Commands\VerifyIntegrityCommand::class,
                Commands\DoctorCommand::class,
            ]);
    }

    /**
     * Perform actions during package booting.
     *
     * Publishes CSS assets to both resources and public directories.
     */
    public function bootingPackage(): void
    {
        $this->publishes([
            __DIR__.'/../resources/css/filament-activity-log.css' => resource_path('vendor/filament-activity-log/filament-activity-log.css'),
        ], 'filament-activity-log-styles');

        $this->publishes([
            __DIR__.'/../resources/css/filament-activity-log.css' => public_path('vendor/filament-activity-log/filament-activity-log.css'),
        ], 'filament-activity-log-public');
    }

    /**
     * Perform actions after the package has been booted.
     *
     * Registers the Activity policy, assets, and icons with Filament.
     */
    public function packageBooted(): void
    {
        Gate::policy(
            app(ActivityQuery::class)->modelClass(),
            ActivityPolicy::class
        );

        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        $activityModel = app(ActivityQuery::class)->modelClass();
        $activityModel::created(function ($activity): void {
            app(AuditMetadata::class)->persist($activity);
            app(AuditRuleEngine::class)->evaluate($activity);
            app(ActivityCache::class)->flush();
        });

        $activityModel::deleted(function (): void {
            app(ActivityCache::class)->flush();
        });
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(ActivityQuery::class);
        $this->app->singleton(ActivityMutation::class);
        $this->app->singleton(ActivityPruner::class);
        $this->app->singleton(ActivityCache::class);
        $this->app->singleton(AuditSchema::class);
        $this->app->singleton(RetentionManager::class);
    }

    /**
     * Get the package name for asset registration.
     *
     * @return string The package name
     */
    protected function getAssetPackageName(): string
    {
        return 'alizharb/filament-activity-log';
    }

    /**
     * Get the assets to register with Filament.
     *
     * @return array<Css> Array of CSS assets
     */
    protected function getAssets(): array
    {
        return [
            Css::make('filament-activity-log', __DIR__.'/../resources/css/filament-activity-log.css'),
        ];
    }
}
