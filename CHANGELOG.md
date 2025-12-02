# Changelog

All notable changes to `filament-activity-log` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-12-02

### Added

#### Core Features

- **Filament v4 Support** - Built with the latest Filament Schema API
- **PHP 8.4 & Laravel 12** - Optimized for the latest tech stack
- **Activity Log Resource** - Full-featured resource for viewing and managing activity logs
- **Timeline View** - Beautiful timeline visualization with customizable icons and colors
- **Dashboard Widgets** - Two powerful widgets for real-time activity monitoring
  - Activity Chart Widget - Visual chart showing activity trends over time
  - Latest Activity Widget - Table widget displaying recent activities
- **Revert Action** - One-click rollback to previous states for update events

#### Components

- **ActivitiesRelationManager** - Drop-in relation manager for any resource
- **ActivityLogTimelineTableAction** - Beautiful timeline modal action for tables
- **ActivityPolicy** - Pre-configured policy for role-based access control
- **ActivityLogResource** - Complete resource with list, view, and filter capabilities

#### Features

- **Advanced Filtering** - Filter by event type, date range, causer, subject type, and log name
- **Global Search** - Search activities from Filament's global search
- **Multi-Language Support** - Available in 6 languages:
  - English (en)
  - Arabic (ar)
  - French (fr)
  - Spanish (es)
  - Portuguese (pt)
  - Hebrew (he)

#### Configuration

- **Extensive Customization** - Configure every aspect via config file:
  - Resource settings (navigation, icons, sorting, pagination)
  - Event icons and colors (created, updated, deleted, restored)
  - Table columns and filters
  - Widget configuration (chart type, colors, polling intervals)
  - Permissions and access control
  - Infolist tabs and entries
- **Fluent API** - Configure plugin settings using fluent methods
- **Event Customization** - Define custom icons and colors for any event type

#### Technical Features

- **Strict Type Declarations** - Full PHP 8.4 type safety
- **PSR-4 Autoloading** - Standard autoloading for optimal performance
- **Comprehensive Tests** - Full test coverage with Pest PHP
- **Code Quality** - Laravel Pint for code formatting, PHPStan for static analysis
- **Service Provider Auto-Discovery** - Automatic Laravel package discovery

#### Documentation

- **Comprehensive README** - Detailed documentation with examples
- **Installation Guide** - Step-by-step setup instructions
- **Configuration Examples** - Complete configuration reference
- **Usage Examples** - Real-world implementation examples
- **Contributing Guidelines** - Clear contribution process
- **Security Policy** - Responsible disclosure guidelines

#### Developer Experience

- **Zero Configuration Required** - Works out of the box with sensible defaults
- **Highly Extensible** - Easy to customize and extend
- **Well-Documented** - Inline documentation and comprehensive README
- **Active Maintenance** - Regular updates and bug fixes

### Technical Details

#### Dependencies

- `php`: ^8.4
- `filament/filament`: ^4.0
- `spatie/laravel-activitylog`: ^4.0
- `illuminate/support`: ^12.0
- `phiki/phiki`: ^1.0

#### Dev Dependencies

- `larastan/larastan`: ^3.8
- `laravel/pint`: ^1.26
- `orchestra/testbench`: ^10.8
- `pestphp/pest`: ^4.1
- `pestphp/pest-plugin-laravel`: ^4.0
- `pestphp/pest-plugin-livewire`: ^4.0
- `phpstan/phpstan`: ^2.1

### Package Information

- **License**: MIT
- **Author**: Ali Harb
- **Repository**: https://github.com/alizharb/filament-activity-log
- **Package Type**: filament-plugin

---

## Future Releases

Future versions will be documented here following the same format.

### Planned Features

- Additional chart types for Activity Chart Widget
- Export functionality for activity logs
- Advanced analytics and reporting
- Custom event types and handlers
- Batch operations support

---

[1.0.0]: https://github.com/alizharb/filament-activity-log/releases/tag/v1.0.0
