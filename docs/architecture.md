# Architecture

## Query boundary

`ActivityQuery` is the only entry point for package-owned activity queries. It resolves the configured Spatie model and applies the per-panel or config-based scope before data reaches resources, widgets, exports, timelines, filters, or maintenance actions.

Tenant applications should treat a missing scope as a deployment error.

`ActivityCache` caches only aggregate values. A scoped query has no cache namespace until `cache.context_key` supplies an explicit tenant/security identity, preventing cross-context cache reuse. Model creation, deletion, backfill, and retention changes rotate the namespace version.

## Controlled mutations

`ActivityMutation` owns revert and restore behavior. It:

- checks activity and subject authorization;
- filters sensitive and system attributes;
- detects changes made after the source activity;
- runs writes in a transaction;
- delegates model reconstruction to `RestoresActivitySubjects`;
- writes a compensating audit activity.

## Metadata projections

New records receive persisted risk metadata, indexed request/IP context, and an HMAC integrity signature. `filament-activity-log:backfill` projects existing records. `filament-activity-log:verify-integrity` detects modified or unsigned rows.

Integrity signatures are tamper-evident, not immutable: direct row deletion cannot be detected without an external ledger or backup.

`RetentionManager` controls legal holds inside the same query boundary and authorization policy. It locks the activity row, updates and re-signs the hold state transactionally, invalidates aggregates, dispatches `RetentionHoldChanged`, and optionally writes a compensating control activity.

## Extension points

- `ScopesActivityQueries` for tenant and security boundaries
- `CollectsActivityContext` for request, trace, device, or application context
- `ProvidesTimelineActivities` for additional timeline sources
- `AuditRule` and `AuditRuleMatched` for alerts and notification integrations
- `RestoresActivitySubjects` for domain-safe reconstruction
- `RetentionHoldChanged` for case-management and compliance integrations

Invalid configuration, integrity, retention, and mutation failures use domain-specific exceptions derived from `ActivityLogException`. Each exception contains a localized operator message plus a stable translation key and structured context suitable for application logs.
