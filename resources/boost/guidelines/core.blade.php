<guideline>
    <title>Filament Activity Log</title>

    <summary>
        Filament Activity Log provides a Filament resource, timeline actions, widgets, exports, and audit-focused helpers on top of spatie/laravel-activitylog.
    </summary>

    <installation>
        Install the package with Composer, register <code>AlizHarb\ActivityLog\ActivityLogPlugin::make()</code> in the Filament panel, and run <code>php artisan filament-activity-log:install</code> when publishing configuration or assets is needed.
    </installation>

    <spatie-activitylog>
        Use Spatie's <code>LogsActivity</code> trait on models that should be audited. For Spatie v5, make sure the official v5 migration has been completed before relying on this package. Spatie v4 stores changes in <code>properties</code>; Spatie v5 stores them in <code>attribute_changes</code>. This package reads both.
    </spatie-activitylog>

    <privacy>
        Do not intentionally log passwords, tokens, API keys, secrets, or private keys. The package redacts configured sensitive fields before display, but redaction is a safety layer and not a reason to store secrets in activity logs.
    </privacy>

    <risk-scoring>
        Use the configured risk system to highlight destructive, security-sensitive, and privacy-sensitive activity. Customize <code>filament-activity-log.risk</code> or provide a resolver class when application-specific scoring is required.
    </risk-scoring>

    <immutable-mode>
        For compliance-heavy panels, enable <code>filament-activity-log.privacy.immutable_mode</code>. This hides delete, bulk delete, prune, restore, and revert actions from the package UI.
    </immutable-mode>

    <authorization>
        Prefer Filament/Laravel authorization for access control. If the application needs a simple package-level rule, configure <code>permissions.custom_authorization</code> with an invokable class instead of a closure so config caching remains safe.
    </authorization>

    <performance>
        Activity tables can grow quickly. Keep filters selective, avoid rendering large JSON payloads in table rows, and add database indexes for common filters such as <code>created_at</code>, <code>event</code>, <code>log_name</code>, <code>causer_type</code>/<code>causer_id</code>, and <code>subject_type</code>/<code>subject_id</code>.
    </performance>

    <filament-usage>
        Use <code>ActivitiesRelationManager</code> to add model-specific history to resources. Use <code>ActivityLogTimelineTableAction</code> when a compact slide-over timeline is better than navigating to the full resource.
    </filament-usage>
</guideline>
