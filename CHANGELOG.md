# Changelog

All notable changes to `filament-activity-log` are documented here.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project follows [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.0.0] - 2026-08-15

### Added

- A tenant-aware activity query boundary shared by resources, widgets, filters, exports, timelines, pruning, and mutation services.
- Persisted risk scores, retention holds, indexed request-ID and IP projections, and an upgrade/backfill migration.
- HMAC integrity signatures and the `filament-activity-log:verify-integrity` command.
- Safe restore and revert services with subject authorization, conflict detection, protected attributes, transactions, and compensating audit records.
- Extensible contracts for query scopes, context collectors, timeline sources, audit rules, and subject restorers.
- Audit-rule events, built-in high-risk detection, scoped aggregate caching, and automatic cache invalidation.
- Authorized single and bulk retention-hold controls that are always respected by package pruning.
- The `filament-activity-log:doctor` installation and security diagnostic command.
- Localized domain exceptions with stable translation keys and structured logging context.
- Fluent main-resource and relation-manager table configuration callbacks.
- Exact key, value, and placeholder parity tests for all 12 shipped locales.
- Security, architecture, operations, upgrade, contribution, and release documentation.

### Changed

- Laravel 12 is now the minimum supported Laravel version; Laravel 13 and PHP 8.5 are explicitly tested.
- Destructive actions and exports now require explicit authorization, and mutations are disabled by default.
- Dashboard polling is disabled by default.
- Activity tables use deferred loading and filters, search-on-blur, persistent state, reorderable columns, localized empty states, and configurable pagination.
- Risk, request, IP, and retention filters use indexed scalar columns after the v2 migration while preserving JSON fallbacks during upgrades.
- Existing `table.columns` configuration remains supported; application-specific Filament behavior can use fluent table callbacks.
- CI now covers compatible Filament 4/5, Spatie Activitylog 4/5, Laravel 12/13, and PHP 8.3-8.5 combinations.

### Security

- Query scopes now consistently protect every package-owned activity query, including exports and aggregate widgets.
- Revert blocks stale overwrites by default and restore/revert exclude sensitive and system attributes.
- Display and export paths consistently redact configured secrets.
- Held records cannot be removed by package pruning.

### Fixed

- Configured activity model connections and tables are honored by migrations and schema detection.
- Fresh installs deterministically run Spatie's table migration before the package upgrade migration.
- Upgrade rollback tolerates partially applied schemas and missing indexes.
- Policies accept any `Authenticatable` implementation and use the correct user-specific gate.

### Removed

- Unused `timeline.show_action` and `widgets.dashboard` configuration keys.
- Unused empty script-data and icon registration hooks.

See [UPGRADE.md](UPGRADE.md) before upgrading an existing installation.

## [1.5.0] - 2026-07-09

### Added

- Configurable audit risk scoring and risk badges.
- Privacy-safe redaction for changes, properties, exports, and action descriptions.
- Immutable audit mode and audit-compliance documentation.

## [1.4.0] - 2026-07-02

### Added

- Configurable causer display attributes, including virtual accessors.

## [1.3.3] - 2026-06-29

### Fixed

- Relationship compatibility, conditional changes-tab visibility, custom activity models, morph maps, and policy type declarations.

## [1.3.2] - 2026-04-16

### Added

- Spatie Activitylog v5 compatibility and Kurdish Sorani translations.

### Fixed

- Widget registration configuration, subject-ID filtering, relation-manager sorting, JSON change fields, timeline PHPStan findings, and config-cache-safe authorizer guidance.

## [1.3.1] - 2026-02-06

### Fixed

- Multi-panel authorization and panel-aware causer resolution.

## [1.3.0] - 2026-01-31

### Added

- Audit dashboard, statistics, activity heatmap, charts, request context, selective revert, subject restore, pruning, timeline pagination, and batch views.
- Global localization coverage for the new resource, action, and dashboard features.

### Fixed

- Resource URL generation and navigation-group handling.

## [1.2.0] - 2026-01-16

### Added

- Filament 5 and Livewire 4 compatibility.
- Strict activity event enums, customizable subject titles, batch support, and cluster integration.

### Changed

- The minimum PHP version became PHP 8.3.

### Fixed

- Cached-view icon rendering and static-analysis findings.

## [1.1.3] - 2025-12-15

### Fixed

- Config serialization when using class-based custom authorization.
- Code-style automation running unnecessarily for tags.

## [1.1.2] - 2025-12-12

### Added

- Configurable custom authorization for activity-log access.

### Fixed

- Plugin navigation settings now take precedence over config defaults.

## [1.1.1] - 2025-12-08

### Fixed

- PHPStan handling for tap-populated activity properties.

## [1.1.0] - 2025-12-08

### Added

- IP and browser tracking, exports, resource links, the activity context tap, and five additional locales.

## [1.0.1] - 2025-12-03

### Changed

- Modernized the timeline UI, CSS, and localization.

### Fixed

- View-string static-analysis errors.

## [1.0.0] - 2025-12-02

### Added

- Initial Filament activity resource, filters, timeline, widgets, relation manager, authorization, configuration, translations, and tests.

[Unreleased]: https://github.com/alizharb/filament-activity-log/compare/v2.0.0...HEAD
[2.0.0]: https://github.com/alizharb/filament-activity-log/compare/v1.5.0...v2.0.0
[1.5.0]: https://github.com/alizharb/filament-activity-log/compare/v1.4.0...v1.5.0
[1.4.0]: https://github.com/alizharb/filament-activity-log/compare/v1.3.3...v1.4.0
[1.3.3]: https://github.com/alizharb/filament-activity-log/compare/v1.3.2...v1.3.3
[1.3.2]: https://github.com/alizharb/filament-activity-log/compare/v1.3.1...v1.3.2
[1.3.1]: https://github.com/alizharb/filament-activity-log/compare/v1.3.0...v1.3.1
[1.3.0]: https://github.com/alizharb/filament-activity-log/compare/v1.2.0...v1.3.0
[1.2.0]: https://github.com/alizharb/filament-activity-log/compare/v1.1.3...v1.2.0
[1.1.3]: https://github.com/alizharb/filament-activity-log/compare/v1.1.2...v1.1.3
[1.1.2]: https://github.com/alizharb/filament-activity-log/compare/v1.1.1...v1.1.2
[1.1.1]: https://github.com/alizharb/filament-activity-log/compare/v1.1.0...v1.1.1
[1.1.0]: https://github.com/alizharb/filament-activity-log/compare/v1.0.1...v1.1.0
[1.0.1]: https://github.com/alizharb/filament-activity-log/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/alizharb/filament-activity-log/releases/tag/v1.0.0
