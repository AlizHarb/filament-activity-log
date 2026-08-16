# Production Operations

## Deployment

Run migrations before deploying code that enables persisted risk filters. Then run:

```bash
php artisan filament-activity-log:doctor
php artisan filament-activity-log:backfill --chunk=500 --only-missing
php artisan filament-activity-log:verify-integrity --chunk=500
```

Backfill and verification honor the configured activity query scope.

For very large tables, resume a reviewed run with `--from-id=<last completed id>`. Backfill queries existing rows without an extra refresh per record and rotates the aggregate cache namespace after completion.

## Performance

The package migration adds indexes for log name, event, subject, causer, risk, request ID, IP address, and creation time. Request and IP investigation filters use projected scalar columns instead of JSON scans. Dashboard aggregates and filter options use a short cache. Scoped installations must configure `cache.context_key`; otherwise caching safely disables itself. Tables defer their first query and filters by default, and search is applied on blur. Keep dashboard polling disabled unless measurements show the database can support it. Use queued Filament exports for large result sets.

## Retention holds

Grant `manage_activity_retention_holds` only to compliance operators. Package pruning excludes held records. Hold changes are transactionally persisted, re-signed, emitted as `RetentionHoldChanged`, and logged as minimal control activities by default.

## Privacy

Display redaction does not remove secrets already stored in the database. Exclude secrets at logging time whenever possible. Enable `auto_context.anonymize_ip` where full IP addresses are unnecessary, and avoid recording query strings.

## Integrity keys

By default signatures use `app.key`. For independent rotation, set `ACTIVITY_LOG_INTEGRITY_KEY` before backfilling. Changing the key invalidates existing signatures, so retain old keys or deliberately re-sign records under a documented rotation procedure.

Spatie v5's optional activity buffer performs bulk inserts that may bypass model-created events. Keep `activitylog.buffer.enabled` disabled when immediate risk projection, integrity signing, or alert evaluation is required, or schedule the backfill command after buffered writes.

## Alerts

Enable `alerts.enabled` to run configured `AuditRule` classes. Listen for `AuditRuleMatched` and deliver findings through Laravel notifications, queues, webhooks, or an incident platform. Event payloads contain the activity and structured finding; redact externally transmitted content according to your policy.
