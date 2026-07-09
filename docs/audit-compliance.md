# Audit, Privacy, and Compliance

Filament Activity Log is designed to sit on top of `spatie/laravel-activitylog` and turn raw activity records into a practical audit cockpit for Filament panels.

## Risk Scoring

Risk scoring helps administrators spot activity that deserves attention. The default scorer considers:

- Event type, such as `deleted`, `force_deleted`, `updated`, or `restored`.
- Log name, such as `security`, `auth`, `permissions`, or `roles`.
- Changed field names, such as password, token, role, permission, email, phone, or address fields.
- Captured request context, such as IP address.

Configure scoring in `config/filament-activity-log.php`:

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
    'fields' => [
        '/password/i' => 45,
        '/token|secret|api_key|private_key/i' => 50,
    ],
],
```

For custom scoring, set `risk.resolver` to an application class. The class should expose a `score($activity): int` method and return a value from `0` to `100`.

## Redaction

Redaction is enabled by default. The package masks configured sensitive fields before values are displayed in:

- Change summaries.
- Raw property views.
- Revert helper text.
- Risk-aware audit screens.

```php
'privacy' => [
    'redacted_value' => '[redacted]',
    'redaction' => [
        'enabled' => true,
        'fields' => [
            'password',
            'token',
            'api_key',
            'secret',
        ],
    ],
],
```

Use patterns when your application has naming conventions for sensitive fields:

```php
'patterns' => [
    '/(^|_)(password|token|secret|key)$/',
],
```

## Immutable Mode

Some teams treat audit logs as append-only records. Enable immutable mode to hide panel actions that mutate or remove audit-related data:

```php
'privacy' => [
    'immutable_mode' => true,
],
```

Immutable mode hides delete, bulk delete, prune, restore, and revert actions from the package UI.

## Retention Recommendations

Use pruning carefully. Before enabling automated cleanup, decide:

- Which logs are operational noise.
- Which logs are security evidence.
- Which logs are required for legal, finance, healthcare, or internal policy reasons.
- Whether high-risk records should be retained longer.

For strict environments, keep immutable mode enabled and perform retention from reviewed application jobs instead of ad-hoc panel actions.

## Spatie v4 and v5 Notes

The package reads changes from both Spatie schemas:

- Spatie v4 stores changes in `properties.attributes` and `properties.old`.
- Spatie v5 stores changes in `attribute_changes`.

The package also handles batch grouping differences:

- Spatie v4 uses the native `batch_uuid` column.
- Spatie v5 uses `properties.group`.

## Operational Advice

Do not log secrets unless your application has a strong reason. Redaction is a display safety layer, not a substitute for careful logging design.

Prefer logging meaningful business events, such as role changes, permission changes, exports, impersonation, billing updates, and destructive model operations.
