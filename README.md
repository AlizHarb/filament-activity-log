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
    <strong>A powerful, feature-rich activity logging solution for FilamentPHP v4</strong><br>
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

| Requirement                                                                                         | Version | Status |
| --------------------------------------------------------------------------------------------------- | ------- | ------ |
| ![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php&logoColor=white)            | 8.2+    | ✅     |
| ![Laravel](https://img.shields.io/badge/Laravel-10+-FF2D20?style=flat&logo=laravel&logoColor=white) | 10+     | ✅     |
| ![Filament](https://img.shields.io/badge/Filament-v4+-F59E0B?style=flat&logo=php&logoColor=white)   | v4+     | ✅     |

**Dependencies:**

- [Spatie Laravel Activitylog](https://spatie.be/docs/laravel-activitylog) - The robust foundation

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
                ->navigationGroup('System'),
        ]);
}
```

### Step 3: Publish Configuration (Optional)

```bash
php artisan vendor:publish --tag="filament-activity-log-config"
```

### Step 4: Publish Assets (Optional)

If you need to customize the CSS or other assets:

```bash
php artisan vendor:publish --tag="filament-activity-log-styles"
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

Restrict access to specific users without setting up a full permission system:

```php
// In config/filament-activity-log.php
'permissions' => [
    'custom_authorization' => fn($user) => $user->id === 1,
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
