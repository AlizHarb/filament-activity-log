<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog;

use Filament\Support\Assets\Asset;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
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
            ->hasViews(static::$viewNamespace);
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
        \Illuminate\Support\Facades\Gate::policy(
            \Spatie\Activitylog\Models\Activity::class,
            \AlizHarb\ActivityLog\Policies\ActivityPolicy::class
        );

        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        FilamentAsset::registerScriptData(
            $this->getScriptData(),
            $this->getAssetPackageName()
        );

        FilamentIcon::register($this->getIcons());
    }

    /**
     * Get the package name for asset registration.
     *
     * @return string|null The package name
     */
    protected function getAssetPackageName(): ?string
    {
        return 'alizharb/filament-activity-log';
    }

    /**
     * Get the assets to register with Filament.
     *
     * @return array<Asset> Array of CSS assets
     */
    protected function getAssets(): array
    {
        return [
            \Filament\Support\Assets\Css::make('filament-activity-log', __DIR__.'/../resources/css/filament-activity-log.css'),
        ];
    }

    /**
     * Get the script data to register with Filament.
     *
     * @return array<string, mixed> Array of script data
     */
    protected function getScriptData(): array
    {
        return [];
    }

    /**
     * Get the icons to register with Filament.
     *
     * @return array<string> Array of icon names
     */
    protected function getIcons(): array
    {
        return [];
    }
}
