# 🚀 Filament Activity Log

<div align="center">
    <img src="https://banners.beyondco.de/Filament%20Activity%20Log.png?theme=light&packageManager=composer+require&packageName=alizharb%2Ffilament-activity-log&pattern=architect&style=style_1&description=Advanced+activity+tracking+for+Filament+v4&md=1&showWatermark=0&fontSize=100px&images=https%3A%2F%2Flaravel.com%2Fimg%2Flogomark.min.svg" alt="Filament Activity Log">
</div>

<div align="center">

[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=for-the-badge)](LICENSE)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/alizharb/filament-activity-log.svg?style=for-the-badge&color=orange)](https://packagist.org/packages/alizharb/filament-activity-log)
[![Total Downloads](https://img.shields.io/packagist/dt/alizharb/filament-activity-log.svg?style=for-the-badge&color=green)](https://packagist.org/packages/alizharb/filament-activity-log)
[![PHP Version](https://img.shields.io/packagist/php-v/alizharb/filament-activity-log.svg?style=for-the-badge&color=purple)](https://packagist.org/packages/alizharb/filament-activity-log)

</div>

<p align="center">
    <strong>A powerful, feature-rich activity logging solution for FilamentPHP v4 & v5</strong><br>
    Seamlessly track, view, and manage user activities with beautiful timelines and insightful dashboards.<br>
    Built on <a href="https://spatie.be/docs/laravel-activitylog">spatie/laravel-activitylog</a>
</p>

---

## 📖 Table of Contents

- [Features](#-features)
- [Requirements](#-requirements)
- [Installation](#-installation)
- [Quick Start](#-quick-start)
- [Core Features](#-core-features)
- [Configuration](#️-configuration)
- [Usage Examples](#-usage-examples)
- [Contributing](#-contributing)
- [License](#-license)

---

## ✨ Features

### 🎯 Core Functionality

- **📦 Full Resource Integration** - Dedicated resource to browse, filter, and search logs
- **⏱️ Timeline View** - Stunning slide-over timeline to visualize record history
- **📊 Insightful Widgets** - Activity charts and latest activity tables
- **🔗 Relation Manager** - Add activity history to any resource
- **🎨 Highly Customizable** - Configure labels, colors, icons, and visibility
- **🔐 Role-Based Access** - Fully compatible with Filament's authorization
- **🌍 Dark Mode Support** - Beautiful in both light and dark modes

---

## 📋 Requirements

| Requirement                                                                                           | Version   | Status |
| ----------------------------------------------------------------------------------------------------- | --------- | ------ |
| ![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?style=flat&logo=php&logoColor=white)              | 8.3+      | ✅     |
| ![Laravel](https://img.shields.io/badge/Laravel-11+-FF2D20?style=flat&logo=laravel&logoColor=white)   | 11+       | ✅     |
| ![Filament](https://img.shields.io/badge/Filament-v4+/v5+-F59E0B?style=flat&logo=php&logoColor=white) | v4+ / v5+ | ✅     |

**Dependencies:**

- [Spatie Laravel Activitylog](https://spatie.be/docs/laravel-activitylog) (^4.0 or ^5.0) - The robust foundation

### Spatie Activitylog Compatibility

| Spatie Version | Support | Notes |
| --- | --- | --- |
| ^4.0 | Full | Legacy support with native `batch_uuid` and `properties`-based tracking |
| ^5.0 | Full | Requires the official v5 upgrade migration (see below) |

> **Important for v5 users:** You must follow [Spatie's official v5 upgrade guide](https://spatie.be/docs/laravel-activitylog) before using this plugin on v5. This includes:
> 1. Adding the `attribute_changes` column
> 2. Dropping the `batch_uuid` column
> 3. Migrating tracked change data from `properties` into `attribute_changes`
>
> The plugin does not support an unmigrated v5 database.

**Key differences between v4 and v5:**

- **Tracked changes:** v4 stores changes in `properties['attributes']` / `properties['old']`. v5 uses the dedicated `attribute_changes` column. The plugin reads from both automatically.
- **Batch grouping:** v4 uses the native `batch_uuid` column. v5 uses custom-property grouping (`properties['group']`) per the official docs. The plugin handles both transparently.
- **Relationships:** v5 renames `activities()` to `activitiesAsSubject()` and `actions()` to `activitiesAsCauser()`. The plugin detects and uses whichever is available.

---

## ⚡ Installation

### Step 1: Install via Composer

```bash
composer require alizharb/filament-activity-log
```

### Step 2: Register the Plugin

Add to your `AdminPanelProvider`:

```php
use AlizHarb\ActivityLog\ActivityLogPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            ActivityLogPlugin::make()
                ->label('Log')
                ->pluralLabel('Logs')
                ->navigationGroup('System')
                ->cluster('System'), // Optional: Group inside a cluster
        ]);
}
```

### Step 3: Install Assets & Config

Run the installation command to publish the configuration, assets, and migrations:

```bash
php artisan filament-activity-log:install
```

---

## 🎯 Quick Start

### 1. Enable Logging on Models

Ensure your models use the `LogsActivity` trait:

```php
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll();
    }
}
```

### 2. Configure Tracking (Optional)

To automatically capture IP addresses and user agent information, add the generic tap to your `config/activitylog.php`:

```php
'activity_logger_taps' => [
    \AlizHarb\ActivityLog\Taps\SetActivityContextTap::class,
],
```

### 3. View Activities

Navigate to the **Logs** resource in your admin panel to see all tracked activities.

---

## 🎯 Core Features

### 📦 Activity Log Resource

A dedicated resource allows you to manage all activity logs.

**Features:**

- ✅ **Advanced Filtering** - Filter by causer, subject, event type, and date
- ✅ **Global Search** - Search through log descriptions and properties
- ✅ **Detailed View** - Inspect every detail of an activity log

### ⏱️ Timeline View

Visualize the history of any record with a beautiful timeline.

**Usage:**
The timeline is available as a table action in the Relation Manager or can be added to any page.

### 📊 Dashboard Widgets

#### Activity Chart Widget

Displays a line chart showing activity trends over time.

```php
use AlizHarb\ActivityLog\Widgets\ActivityChartWidget;

public function getWidgets(): array
{
    return [
        ActivityChartWidget::class,
    ];
}
```

#### Latest Activity Widget

Shows a list of the most recent activities.

```php
use AlizHarb\ActivityLog\Widgets\LatestActivityWidget;

public function getWidgets(): array
{
    return [
        LatestActivityWidget::class,
    ];
}
```

### 🔗 Relation Manager

Add an activity log history table to any of your existing resources (e.g., `UserResource`).

```php
use AlizHarb\ActivityLog\RelationManagers\ActivitiesRelationManager;

public static function getRelations(): array
{
    return [
        ActivitiesRelationManager::class,
    ];
}
```

### 🏷️ Customizable Subject Titles

The package automatically checks for `name`, `title`, or `label` attributes on your models.
For more control, implement the `HasActivityLogTitle` interface on your model:

```php
use AlizHarb\ActivityLog\Contracts\HasActivityLogTitle;

class User extends Model implements HasActivityLogTitle
{
    public function getActivityLogTitle(): string
    {
        return "User: {$this->email}";
    }
}
```

### 📚 Activity Grouping / Batch Support

Automatically group activities from a single job or request. Use the **View Batch** action in the Activity Log table to inspect all related activities.

- **Spatie v4:** Uses the native `batch_uuid` column for grouping.
- **Spatie v5:** Uses custom-property grouping (`properties['group']`), since upstream batch support was removed in v5. The plugin handles this automatically via the `SetActivityContextTap`.

---

## ⚙️ Configuration

You can customize almost every aspect of the package via the `filament-activity-log.php` config file.

📚 **For detailed configuration instructions, including navigation groups and custom authorization, see [CONFIGURATION.md](CONFIGURATION.md)**

### Customizing Table Columns

```php
'table' => [
    'columns' => [
        'log_name' => [
            'visible' => true,
            'searchable' => true,
            'sortable' => true,
        ],
        // ...
    ],
],
```

### Customizing Widgets

```php
'widgets' => [
    'activity_chart' => [
        'enabled' => true,
        'days' => 30,
        'fill_color' => 'rgba(16, 185, 129, 0.1)',
        'border_color' => '#10b981',
    ],
    'latest_activity' => [
        'enabled' => true,
        'limit' => 10,
    ],
],
```

### Custom Authorization

Restrict access to specific users by implementing a custom authorizer invokable class:

```php
// app/Authorizer/ActivityLogAuthorizer.php
namespace App\Authorizors;

class ActivityLogAuthorizer
{
    public function __invoke(User $user): bool
    {
        // Define your custom logic to determine if the user can access the activity log.
         return $user->id === 1;
    }
}
```

Then register it in the config:

```php
// config/filament-activity-log.php
'permissions' => [
    'custom_authorization' => \App\Authorizer\ActivityLogAuthorizer::class,
],
```

See [CONFIGURATION.md](CONFIGURATION.md) for more examples.

---

## 🤝 Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

### Development Setup

```bash
# Clone repository
git clone https://github.com/alizharb/filament-activity-log.git

# Install dependencies
composer install

# Run tests
composer test

# Format code
composer format
```

---

## 💖 Sponsor This Project

If this package helps you, consider sponsoring its development:

<div align="center">

[![Sponsor on GitHub](https://img.shields.io/badge/Sponsor-GitHub-red?style=for-the-badge&logo=github-sponsors&logoColor=white)](https://github.com/sponsors/alizharb)

</div>

Your support helps maintain and improve this package! 🙏

---

## 🐛 Issues & Support

- 🐛 **Bug Reports**: [Create an issue](https://github.com/alizharb/filament-activity-log/issues)
- 💡 **Feature Requests**: [Request a feature](https://github.com/alizharb/filament-activity-log/issues)
- 💬 **Discussions**: [Join the discussion](https://github.com/alizharb/filament-activity-log/discussions)

---

## 📄 License

This project is licensed under the **MIT License** - see the [LICENSE](LICENSE.md) file for details.

---

## 🙏 Acknowledgments

- [FilamentPHP](https://filamentphp.com)
- [Spatie Activitylog](https://spatie.be/docs/laravel-activitylog)
- [Ali Harb](https://github.com/alizharb)
- [All Contributors](../../contributors)
