# Upgrading to v2

Version 2 is security-first. Viewing remains available by default, while mutations and exports require explicit authorization.

Version 2 requires Laravel 12 or 13. The tested combinations are Laravel 12 with Spatie Activitylog 4 and Filament 4 or 5, and Laravel 13 with Spatie Activitylog 5 and Filament 5 on PHP 8.4 and 8.5.

## 1. Publish and run the audit migration

```bash
php artisan vendor:publish --tag=filament-activity-log-migrations
php artisan migrate
php artisan filament-activity-log:backfill
php artisan filament-activity-log:verify-integrity
```

The migration adds persisted risk metadata, retention holds, integrity signatures, indexed request IDs and IP addresses, and other high-value query indexes to Spatie's configured activity table.

The backfill copies existing request IDs and IP addresses out of JSON properties into their indexed columns. Keep the original properties for compatibility; the package reads both layouts during the upgrade.

## 2. Configure tenant isolation

Applications with tenant-separated data must configure one global activity query scope. This scope is applied to resources, widgets, filters, timelines, pruning, and exports.

```php
ActivityLogPlugin::make()->scopeActivitiesUsing(
    fn (Builder $query, ?Model $tenant): Builder => $query->when(
        $tenant,
        fn (Builder $query): Builder => $query->where('tenant_id', $tenant->getKey()),
    ),
);
```

For config-cached applications, use a class implementing `ScopesActivityQueries` in `query.scope`.

If aggregate caching should remain enabled with a query scope, configure `cache.context_key` as an invokable class that returns a stable tenant/security-context identifier. The package disables scoped caching when this key is absent.

## 3. Review mutation permissions

Delete, prune, restore, revert, and bulk deletion are disabled by default. To enable controlled mutations:

1. set `mutations.enabled` to `true`;
2. enable the individual table action;
3. enable permissions or configure `mutations.custom_authorization`;
4. ensure subject model policies authorize `update` and `create`;
5. provide a custom `RestoresActivitySubjects` implementation for models with required fields or relationships.

Stale reverts and sensitive attributes are blocked by default.

## 4. Authorize exports

Set `permissions.enabled` and grant `export_activity_logs`, or explicitly set `permissions.allow_export_when_disabled` after reviewing the exposure risk.

## 5. Configure retention holds

Grant `manage_activity_retention_holds` only to compliance operators. Holds are independent of subject restore/revert mutations, remain available in immutable UI mode, and are always excluded from package pruning.

## 6. Validate the installation

```bash
php artisan filament-activity-log:doctor
```

## 7. Review removed configuration

Version 2 removes only settings that were never read (`timeline.show_action` and `widgets.dashboard`). Existing `table.columns` settings remain supported. Prefer `configureTableUsing()` and `configureRelationManagerTableUsing()` for advanced Filament behavior instead of growing the published config.
