# Installation Guide

This guide will walk you through installing and setting up the Filament Activity Log package.

## Requirements

Before installing, ensure your system meets these requirements:

- **PHP**: 8.4 or higher
- **Laravel**: 12.x
- **FilamentPHP**: 4.x
- **Spatie Laravel Activity Log**: 4.x

## Installation Steps

### Step 1: Install via Composer

Install the package using Composer:

```bash
composer require alizharb/filament-activity-log
```

> **Note for Anystack Customers**: If you purchased this package through Anystack, you'll need to authenticate with your Anystack credentials when prompted by Composer. Follow the authentication instructions provided in your Anystack purchase confirmation email.

### Step 2: Install Spatie Activity Log

If you haven't already installed the Spatie Laravel Activity Log package, install it now:

```bash
# Publish the migration
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"

# Run the migration
php artisan migrate
```

For more information about Spatie Activity Log, see their [official documentation](https://spatie.be/docs/laravel-activitylog/v4/installation-and-setup).

### Step 3: Publish Configuration (Optional)

Publish the configuration file to customize the package:

```bash
php artisan vendor:publish --tag="filament-activity-log-config"
```

This creates a `config/filament-activity-log.php` file where you can customize:

- Resource settings (navigation, icons, sorting)
- Event icons and colors
- Table columns and filters
- Widget configuration
- Permissions
- And much more!

### Step 4: Publish Translations (Optional)

If you want to customize the translations:

```bash
php artisan vendor:publish --tag="filament-activity-log-translations"
```

This publishes translation files to `lang/vendor/filament-activity-log/`.

### Step 5: Publish Views (Optional)

To customize the timeline view:

```bash
php artisan vendor:publish --tag="filament-activity-log-views"
```

## Quick Start

### Register the Resource

Add the `ActivityLogResource` to your Filament panel provider:

```php
use AlizHarb\ActivityLog\Resources\ActivityLogs\ActivityLogResource;

public function panel(Panel $panel): Panel
{
    return $panel
        ->resources([
            ActivityLogResource::class,
        ]);
}
```

### Enable Activity Logging on Models

Add the `LogsActivity` trait to your models:

```php
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Post extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'content', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
```

### Add Dashboard Widgets (Optional)

Enable the dashboard widgets in your panel provider:

```php
use AlizHarb\ActivityLog\Widgets\ActivityChartWidget;
use AlizHarb\ActivityLog\Widgets\LatestActivityWidget;

public function panel(Panel $panel): Panel
{
    return $panel
        ->widgets([
            ActivityChartWidget::class,
            LatestActivityWidget::class,
        ]);
}
```

### Add Relation Manager to Resources (Optional)

Display activity logs within your resource pages:

```php
use AlizHarb\ActivityLog\RelationManagers\ActivitiesRelationManager;

class UserResource extends Resource
{
    public static function getRelations(): array
    {
        return [
            ActivitiesRelationManager::class,
        ];
    }
}
```

## Verification

After installation, verify everything is working:

1. **Visit the Activity Logs page** in your Filament panel
2. **Create/Update/Delete a model** that has activity logging enabled
3. **Check the Activity Logs** to see the recorded activity
4. **View the dashboard widgets** (if enabled) to see activity trends

## Troubleshooting

### Activities Not Appearing

If activities aren't being logged:

1. **Check the model** has the `LogsActivity` trait
2. **Verify the migration** ran successfully (`activity_log` table exists)
3. **Check the `getActivitylogOptions()`** method is configured correctly
4. **Ensure the model** has the `$fillable` or `$guarded` property set

### Permission Errors

If you're getting permission errors:

1. **Check the policy** is registered in your `AuthServiceProvider`
2. **Verify permissions** are configured correctly in `config/filament-activity-log.php`
3. **Ensure the user** has the required permissions

### Widgets Not Showing

If widgets aren't appearing:

1. **Check widgets are enabled** in `config/filament-activity-log.php`
2. **Verify widgets are registered** in your panel provider
3. **Check the `canView()` method** returns `true`

## Next Steps

- Read the [README](README.md) for detailed usage instructions
- Check the [Configuration Guide](config/filament-activity-log.php) for customization options
- Review the [Changelog](CHANGELOG.md) for version history
- See [Contributing Guidelines](CONTRIBUTING.md) if you want to contribute

## Support

If you encounter any issues:

- **Check the documentation**: [README.md](README.md)
- **Search existing issues**: [GitHub Issues](https://github.com/alizharb/filament-activity-log/issues)
- **Create a new issue**: [Report a Bug](https://github.com/alizharb/filament-activity-log/issues/new)
- **Security issues**: See [SECURITY.md](SECURITY.md)

## Anystack Customers

If you purchased this package through Anystack:

- **Authentication**: Use your Anystack credentials when Composer prompts for authentication
- **Updates**: Updates are automatically available through Anystack
- **Support**: Contact Anystack support for billing and licensing questions
- **Technical Support**: Use GitHub Issues for technical questions

---

**Congratulations!** 🎉 You've successfully installed Filament Activity Log. Start tracking activities in your application!
