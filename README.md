# Filament Activity Log

[![Latest Version on Packagist](https://img.shields.io/packagist/v/alizharb/filament-activity-log.svg?style=flat-square)](https://packagist.org/packages/alizharb/filament-activity-log)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/alizharb/filament-activity-log/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/alizharb/filament-activity-log/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/alizharb/filament-activity-log.svg?style=flat-square)](https://packagist.org/packages/alizharb/filament-activity-log)

**Filament Activity Log** is a powerful, feature-rich activity logging solution designed specifically for **FilamentPHP v4**. Built on top of the robust `spatie/laravel-activitylog`, it provides a seamless integration into your Filament admin panel with advanced timeline views, insightful dashboard widgets, and comprehensive configuration options.

## 🚀 Features

- **Full Filament v4 Support**: Designed and optimized for the latest Filament version.
- **Laravel 12 Ready**: Fully compatible with Laravel 12 and PHP 8.2+.
- **Beautiful Timeline View**: Visualize activity history with an intuitive timeline interface.
- **Advanced Dashboard Widgets**:
  - **Activity Chart**: Interactive line chart showing activity trends over time.
  - **Latest Activity**: Real-time table widget displaying recent events.
- **Deep Integration**:
  - Global Search support (search by causer or subject).
  - Automatic resource registration.
  - Configurable navigation (Group, Icon, Sort, Badge).
- **Highly Configurable**: Customize labels, colors, icons, and visibility via a simple config file.
- **Role-Based Access Control**: Integrated with Laravel policies for secure access management.
- **Dark Mode Support**: Looks stunning in both light and dark modes.

## 📦 Installation

You can install the package via composer:

```bash
composer require alizharb/filament-activity-log
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="filament-activity-log-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="filament-activity-log-config"
```

## 🔧 Configuration

This is the contents of the published config file:

```php
return [
    'resource' => [
        'class' => \AlizHarb\ActivityLog\Resources\ActivityLogs\ActivityLogResource::class,
        'group' => 'System',
        'sort' => null,
        'navigation_icon' => 'heroicon-o-clipboard-document-list',
        'navigation_count_badge' => true,
    ],

    'widgets' => [
        'enabled' => true,
        'widgets' => [
            \AlizHarb\ActivityLog\Widgets\ActivityChartWidget::class,
            \AlizHarb\ActivityLog\Widgets\LatestActivityWidget::class,
        ],

        'activity_chart' => [
            'enabled' => true,
            'heading' => 'Activity Over Time',
            'days' => 30,
            'type' => 'line', // line, bar, etc.
            'polling_interval' => null, // '10s', '1m'
        ],

        'latest_activity' => [
            'enabled' => true,
            'heading' => 'Latest Activities',
            'limit' => 10,
            'polling_interval' => null,
            'columns' => [
                'event' => true,
                'causer' => true,
                'subject_type' => true,
                'description' => true,
                'created_at' => true,
            ],
        ],
    ],

    'datetime_format' => 'M d, Y H:i:s',
];
```

## ⚡ Usage

### Registering the Plugin

Add the `ActivityLogPlugin` to your Filament Panel provider (usually `AdminPanelProvider.php`):

```php
use AlizHarb\ActivityLog\ActivityLogPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugins([
            ActivityLogPlugin::make()
                ->label('Activity Log')
                ->pluralLabel('Activity Logs')
                ->navigationGroup('System')
                ->navigationIcon('heroicon-o-shield-check')
                ->navigationSort(3)
                ->navigationCountBadge(true),
        ]);
}
```

### Logging Activity

Since this package uses `spatie/laravel-activitylog` under the hood, you can use all its features. For example, in your Eloquent models:

```php
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }
}
```

### Dashboard Widgets

The package comes with two widgets that you can add to your dashboard:

1.  **ActivityChartWidget**: Shows a trend of activities.
2.  **LatestActivityWidget**: Shows a list of recent activities.

You can enable/disable them in the config or register them manually in your Panel provider if you prefer granular control.

## 🛡️ Security

If you discover any security related issues, please email harbzali@gmail.com instead of using the issue tracker.

## 🙏 Acknowledgments

- This package is built on top of the excellent [spatie/laravel-activitylog](https://github.com/spatie/laravel-activitylog) package.
- Special thanks to the [Filament](https://filamentphp.com) team for creating an amazing admin panel.

## 👤 Author

- [Ali Harb](https://github.com/alizharb)

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
