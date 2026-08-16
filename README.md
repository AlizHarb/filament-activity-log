# 🚀 Filament Activity Log

<div align="center">
    <img src="https://banners.beyondco.de/Filament%20Activity%20Log.png?theme=light&packageManager=composer+require&packageName=alizharb%2Ffilament-activity-log&pattern=architect&style=style_1&description=Security-first+audit+control+for+Filament+v4+%26+v5&md=1&showWatermark=0&fontSize=100px&images=https%3A%2F%2Flaravel.com%2Fimg%2Flogomark.min.svg" alt="Filament Activity Log">
</div>

<div align="center">

[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=for-the-badge)](LICENSE.md)
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
- [Contributing](#-contributing)
- [License](#-license)

---

## ✨ Features

### 🎯 Core Functionality

- **📦 Full Resource Integration** - Dedicated resource to browse, filter, and search logs
- **⏱️ Timeline View** - Stunning slide-over timeline to visualize record history
- **📊 Insightful Widgets** - Activity charts and latest activity tables
- **🛡️ Audit Risk Scoring** - Surface high-risk and critical activity before it gets buried
- **🔒 Privacy-Safe Redaction** - Mask secrets in diffs, raw data, exports, and UI views
- **🏛️ Compliance Mode** - Optional immutable mode for audit trails that should not be changed from the panel
- **🏢 Tenant-Safe Query Boundary** - Apply one security scope across every resource, widget, export, timeline, and maintenance action
- **🧭 Investigation Filters** - Persisted risk levels, retention holds, request IDs, IP addresses, subjects, causers, and batches
- **⚖️ Authorized Retention Holds** - Place or release single and bulk legal holds while protected records stay outside pruning
- **⚡ Scope-Safe Aggregate Cache** - Cache dashboard analytics without crossing tenant or security boundaries
- **🔏 Tamper-Evident Signatures** - Verify activity integrity with HMAC signatures and an Artisan audit command
- **🚨 Extensible Alert Rules** - Dispatch structured findings to Laravel notifications, queues, or incident tooling
- **🧩 Extension Contracts** - Add context collectors, timeline sources, audit rules, and domain-safe subject restorers
- **🔗 Relation Manager** - Add activity history to any resource
- **🎨 Highly Customizable** - Configure labels, colors, icons, and visibility
- **🔐 Role-Based Access** - Fully compatible with Filament's authorization
- **🌍 Dark Mode Support** - Beautiful in both light and dark modes

---

## 📋 Requirements

| Requirement                                                                                           | Version   | Status |
| ----------------------------------------------------------------------------------------------------- | --------- | ------ |
| ![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?style=flat&logo=php&logoColor=white)              | 8.3+      | ✅     |
| ![Laravel](https://img.shields.io/badge/Laravel-12%2F13-FF2D20?style=flat&logo=laravel&logoColor=white) | 12 / 13   | ✅     |
| ![Filament](https://img.shields.io/badge/Filament-v4+/v5+-F59E0B?style=flat&logo=php&logoColor=white) | v4+ / v5+ | ✅     |

**Dependencies:**

- [Spatie Laravel Activitylog](https://spatie.be/docs/laravel-activitylog) (^4.0 or ^5.0) - The robust foundation

### Spatie Activitylog Compatibility

| Spatie Version | Support | Notes |
| --- | --- | --- |
| ^4.0 | Full | Legacy support with native `batch_uuid` and `properties`-based tracking |
| ^5.0 | Full on Laravel 13 / PHP 8.4–8.5 | Requires the official v5 upgrade migration (see below) |

The release matrix verifies Laravel 12 with Filament 4 or 5 and Spatie 4, plus Laravel 13 with Filament 5 and Spatie 5 on PHP 8.4 and 8.5. Version 2 does not claim Laravel 11 support.

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
use App\Filament\Clusters\SystemCluster;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            ActivityLogPlugin::make()
                ->label('Log')
                ->pluralLabel('Logs')
                ->navigationGroup('System')
                ->cluster(SystemCluster::class), // Optional: Group inside a cluster
        ]);
}
```

### Step 3: Publish Config and Migrations

Run the installation command to publish the configuration, assets, and migrations:

```bash
php artisan filament-activity-log:install
```

For an existing installation upgrading to v2:

```bash
php artisan vendor:publish --tag=filament-activity-log-migrations
php artisan migrate
php artisan filament-activity-log:backfill
php artisan filament-activity-log:verify-integrity
php artisan filament-activity-log:doctor
```

See the [v2 upgrade guide](UPGRADE.md) before enabling mutations or exports.

---

## 🎯 Quick Start

### 1. Enable Logging on Models

Ensure your models use the `LogsActivity` trait:

```php
// Spatie v5:
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

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

On Spatie v4, import `Spatie\Activitylog\Traits\LogsActivity` and `Spatie\Activitylog\LogOptions` instead. The package supports both schemas; these upstream namespaces differ between major versions.

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
- ✅ **Risk Badges** - Quickly spot sensitive, destructive, or security-related changes
- ✅ **Privacy Redaction** - Sensitive fields are masked before display by default

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

### 🛡️ Audit Risk Scoring

Every activity can receive a configurable risk score based on event type, log name, changed fields, and captured context. The resource displays a risk badge so administrators can quickly notice destructive, security-sensitive, or privacy-sensitive actions.

```php
'risk' => [
    'enabled' => true,
    'events' => [
        'deleted' => 45,
        'force_deleted' => 70,
    ],
    'log_names' => [
        'security' => 35,
        'permissions' => 40,
    ],
],
```

For advanced applications, provide a custom resolver class in `risk.resolver` and implement your own `score($activity): int` logic.

### 🔒 Privacy-Safe Redaction

Sensitive values are redacted by default before they appear in changes, raw properties, action helper text, and supported export fields.

```php
'privacy' => [
    'redacted_value' => '[redacted]',
    'redaction' => [
        'enabled' => true,
        'fields' => ['password', 'token', 'api_key', 'secret'],
    ],
],
```

This keeps audit screens useful while reducing the chance that administrators accidentally expose credentials or secrets.

### 🏛️ Immutable Audit Mode

For stricter environments, enable immutable mode to hide destructive panel actions like delete, prune, restore, and revert:

```php
'privacy' => [
    'immutable_mode' => true,
],
```

> This setting protects the package UI. It does not make the underlying database immutable. Version 2 integrity signatures detect row modification, while database permissions, backups, and external retention controls remain operational responsibilities.

### 🏢 Tenant and Security Scoping

All package-owned queries flow through one boundary. Configure it once for tenant-separated applications:

```php
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

ActivityLogPlugin::make()
    ->scopeActivitiesUsing(
        fn (Builder $query, ?Model $tenant): Builder => $query->when(
            $tenant,
            fn (Builder $query): Builder => $query->where('tenant_id', $tenant->getKey()),
        ),
    );
```

Use a class implementing `ScopesActivityQueries` in config when the configuration must be cacheable.

### ⚡ Scope-Safe Caching

Dashboard aggregates and filter options use a short cache by default. Global installations are safe automatically. When a query scope is configured, caching switches off unless the application supplies a stable security-context key:

```php
'cache' => [
    'ttl' => 60,
    'context_key' => fn (?Model $tenant): ?string => $tenant?->getMorphClass().':'.$tenant?->getKey(),
],
```

Use an invokable class instead of a closure when running `php artisan config:cache`. New and deleted activities, metadata backfills, and retention changes invalidate the aggregate cache.

### 🔐 Controlled Restore and Revert

Mutating actions are disabled by default. Version 2 checks activity and subject policies, blocks stale overwrites, removes sensitive/system attributes, uses transactions, and records compensating activities. Models with required fields or relationships should provide a `RestoresActivitySubjects` implementation.

### 🔏 Integrity and Alerts

New records receive persisted risk metadata and tamper-evident signatures after the package migration is installed. Run `filament-activity-log:verify-integrity` during scheduled compliance checks. Enable `alerts.enabled` and listen for `AuditRuleMatched` to deliver findings through your preferred notification channel.

### ⚖️ Retention Holds

Grant `manage_activity_retention_holds` to authorized compliance staff to place or release holds from the activity table. Hold changes are re-signed, emit `RetentionHoldChanged`, and create a minimal compensating audit record by default. Prune operations always exclude held records.

---

## ⚙️ Configuration

You can customize almost every aspect of the package via the `filament-activity-log.php` config file.

Version 2 keeps the existing column configuration for upgrade compatibility. New application-specific table behavior belongs in fluent callbacks, keeping the published config stable and avoiding a new option for every Filament table feature.

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

The resource and relation manager ship with deferred loading and filters, search-on-blur, persistent search/filter/sort state, reorderable columns, a two-column manager, striped rows, improved pagination, and localized empty states. Override any table behavior per panel:

```php
use Filament\Tables\Table;

ActivityLogPlugin::make()
    ->configureTableUsing(fn (Table $table): Table => $table
        ->deferLoading(false)
        ->poll('30s'))
    ->configureRelationManagerTableUsing(fn (Table $table): Table => $table
        ->paginated([10, 25]));
```

This callback is also the recommended way to add application-owned columns, filters, groups, or actions without publishing and modifying package classes.

### Customizing Widgets

```php
'widgets' => [
    'widgets' => [
        \AlizHarb\ActivityLog\Widgets\ActivityChartWidget::class,
        \AlizHarb\ActivityLog\Widgets\LatestActivityWidget::class,
    ],
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
namespace App\Authorizer;

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

### Advanced Documentation

- [Audit, Privacy, and Compliance](docs/audit-compliance.md)
- [v2 Upgrade Guide](UPGRADE.md)
- [Architecture and Extension Points](docs/architecture.md)
- [Production Operations](docs/operations.md)
- [Laravel Boost Guidelines](resources/boost/guidelines/core.blade.php)

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
- [All Contributors](https://github.com/alizharb/filament-activity-log/graphs/contributors)
