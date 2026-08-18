<?php

declare(strict_types=1);
use AlizHarb\ActivityLog\Pages\UserActivitiesPage;
use AlizHarb\ActivityLog\Resources\ActivityLogs\ActivityLogResource;
use AlizHarb\ActivityLog\Rules\HighRiskActivityRule;
use AlizHarb\ActivityLog\Support\DefaultSubjectRestorer;
use AlizHarb\ActivityLog\Support\RequestContextCollector;
use AlizHarb\ActivityLog\Timeline\SpatieActivitySource;
use AlizHarb\ActivityLog\Widgets\ActivityChartWidget;
use AlizHarb\ActivityLog\Widgets\ActivityHeatmapWidget;
use AlizHarb\ActivityLog\Widgets\ActivityStatsWidget;
use AlizHarb\ActivityLog\Widgets\LatestActivityWidget;
use Illuminate\Support\Env;

return [
    /*
    |--------------------------------------------------------------------------
    | Activity Query Boundary
    |--------------------------------------------------------------------------
    |
    | Set a class implementing ScopesActivityQueries to enforce tenant or
    | security isolation across resources, widgets, exports, and actions.
    | A per-panel callable may also be registered with scopeActivitiesUsing().
    |
    */
    'query' => [
        'scope' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Aggregate Cache
    |--------------------------------------------------------------------------
    |
    | Dashboard aggregates and filter options are cached briefly. When a query
    | scope is configured, caching is disabled unless context_key returns a
    | stable tenant/security-context identifier.
    |
    */
    'cache' => [
        'ttl' => 60,
        'store' => null,
        'context_key' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Controlled Mutations
    |--------------------------------------------------------------------------
    |
    | Restoring and reverting business records is intentionally opt-in. The
    | default workflow checks the subject policy, blocks stale overwrites, and
    | writes a compensating audit record without copying sensitive values.
    |
    */
    'mutations' => [
        'enabled' => false,
        'custom_authorization' => null,
        'authorize_subject' => true,
        'allow_sensitive_attributes' => false,
        'log_compensating_activity' => true,
        'log_name' => 'audit-control',
        'revert' => [
            'block_on_conflict' => true,
            'denied_attributes' => ['id', 'created_at', 'updated_at', 'deleted_at'],
        ],
        'restore' => [
            'restorer' => DefaultSubjectRestorer::class,
            'allowed_attributes' => null,
            'denied_attributes' => ['created_at', 'updated_at', 'deleted_at'],
            'preserve_primary_key' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resource Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the Activity Log resource.
    |
    */
    'resource' => [
        'class' => ActivityLogResource::class,
        'group' => null,
        'sort' => null,
        'default_sort_column' => 'created_at',
        'default_sort_direction' => 'desc',
        'navigation_count_badge' => false,
        'navigation_icon' => 'heroicon-o-rectangle-stack',
        'global_search' => [
            'enabled' => true,
            'attributes' => ['log_name', 'description', 'subject_type', 'event'],
        ],
        'pagination' => [
            'options' => [10, 25, 50, 100],
            'default' => 25,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Activity Log Icons & Colors
    |--------------------------------------------------------------------------
    |
    | Define the icons and colors for different activity events.
    | You can add custom events here as well.
    |
    */
    'events' => [
        'created' => [
            'icon' => 'heroicon-m-plus',
            'color' => 'success',
        ],
        'updated' => [
            'icon' => 'heroicon-m-pencil',
            'color' => 'warning',
        ],
        'deleted' => [
            'icon' => 'heroicon-m-trash',
            'color' => 'danger',
        ],
        'restored' => [
            'icon' => 'heroicon-m-arrow-uturn-left',
            'color' => 'gray',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | DateTime Format
    |--------------------------------------------------------------------------
    |
    | The format used for displaying dates in the timeline and table.
    |
    */
    'datetime_format' => 'M d, Y H:i:s',

    /*
    |--------------------------------------------------------------------------
    | Table Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the activity log table.
    |
    */
    'table' => [
        'columns' => [
            'log_name' => [
                'visible' => true,
                'searchable' => true,
                'sortable' => true,
            ],
            'event' => [
                'visible' => true,
                'searchable' => true,
                'sortable' => true,
            ],
            'risk' => [
                'visible' => true,
                'sortable' => true,
            ],
            'subject_type' => [
                'visible' => true,
                'searchable' => true,
                'sortable' => true,
            ],
            'subject_id' => [
                'visible' => true,
                'searchable' => true,
                'sortable' => true,
            ],
            'causer' => [
                'visible' => true,
                'searchable' => true,
                'sortable' => true,
            ],
            'description' => [
                'visible' => true,
                'searchable' => true,
                'limit' => 50,
            ],
            'created_at' => [
                'visible' => true,
                'searchable' => false,
                'sortable' => true,
            ],
            'ip_address' => [
                'visible' => true,
                'searchable' => true,
            ],
            'user_agent' => [
                'visible' => true,
                'searchable' => false,
            ],
        ],
        'filters' => [
            'log_name' => true,
            'event' => true,
            'risk' => true,
            'retention_hold' => true,
            'created_at' => true,
            'causer' => true,
            'subject_type' => true,
            'subject_id' => true,
            'request_id' => true,
            'ip_address' => true,
        ],
        'actions' => [
            'timeline' => true,
            'view' => true,
            'revert' => false,
            'restore' => false,
            'delete' => false,
            'export' => true,
            'prune' => false,
            'retention_hold' => true,
        ],
        'bulk_actions' => [
            'delete' => false,
            'retention_hold' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Infolist Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the activity log infolist.
    |
    */
    'infolist' => [
        'tabs' => [
            'overview' => true,
            'changes' => true,
            'raw_data' => true,
        ],
        'entries' => [
            'log_name' => true,
            'event' => true,
            'risk' => true,
            'created_at' => true,
            'causer' => true,
            'subject' => true,
            'description' => true,
            'properties_attributes' => true,
            'properties_old' => true,
            'properties_raw' => true,
            'ip_address' => true,
            'request_id' => true,
            'user_agent' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Timeline Action
    |--------------------------------------------------------------------------
    |
    | Configuration for the timeline action.
    |
    */
    'timeline' => [
        'icon' => 'heroicon-m-clock',
        'limit' => 50,
        'sources' => [
            SpatieActivitySource::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the permissions.
    |
    | You can use 'custom_authorization' to define your own authorization logic.
    | For example, to restrict access to only user ID 1:
    |
    | 'custom_authorization' => fn($user) => $user->id === 1,
    |
    | Or to allow super admins only:
    |
    | 'custom_authorization' => fn($user) => $user->hasRole('super_admin'),
    |
    | If 'custom_authorization' is set, it takes precedence over the 'enabled'
    | and permission checks.
    |
    */
    'permissions' => [
        'enabled' => false,

        /**
         * Custom invokable authorizer class for accessing the activity log.
         *
         * If set, this takes precedence over the 'enabled' setting and permission checks.
         * This invokable receives the authenticated user and should return a boolean.
         *
         * Example: 'App\Support\ActivityLogAuthorization' (class with __invoke(User $user): bool)
         */
        'custom_authorization' => null,

        'view_any' => 'view_any_activity',
        'view' => 'view_activity',
        'create' => 'create_activity',
        'update' => 'update_activity',
        'delete' => 'delete_activity',
        'delete_any' => 'delete_any_activity',
        'prune' => 'prune_activity_logs',
        'restore' => 'restore_activity',
        'force_delete' => 'force_delete_activity',
        'export' => 'export_activity_logs',
        'hold' => 'manage_activity_retention_holds',
        'allow_export_when_disabled' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Pages Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for pages provided by the plugin.
    |
    */
    'pages' => [
        'user_activities' => [
            'enabled' => true,
            'class' => UserActivitiesPage::class,
            'navigation_label' => null, // null uses translation key
            'navigation_group' => null, // null uses resource group
            'navigation_sort' => 2,
            'polling_interval' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Widgets Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for dashboard widgets.
    |
    */
    'widgets' => [
        'enabled' => true,
        'widgets' => [
            ActivityChartWidget::class,
            LatestActivityWidget::class,
            ActivityHeatmapWidget::class,
            ActivityStatsWidget::class,
        ],

        /**
         * Activity Chart Widget Configuration
         */
        'activity_chart' => [
            'enabled' => true,
            'heading' => null,
            'sort' => 1,
            'max_height' => '300px',
            'polling_interval' => null, // e.g., '10s', '1m', null to disable
            'days' => 30,
            'type' => 'line', // 'line', 'bar', 'pie', 'doughnut', 'polarArea', 'radar'
            'label' => null,
            'fill' => true,
            'tension' => 0.3, // Curve smoothness (0 = straight lines, 0.4 = smooth curves)
            'border_color' => '#10b981', // Chart line/border color
            'fill_color' => 'rgba(16, 185, 129, 0.1)', // Chart fill color
            'date_format' => 'M d', // Date format for labels
            'options' => [
                'plugins' => [
                    'legend' => [
                        'display' => false,
                    ],
                ],
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                        'ticks' => [
                            'precision' => 0,
                        ],
                    ],
                ],
            ],
        ],

        /**
         * Latest Activity Widget Configuration
         */
        'latest_activity' => [
            'enabled' => true,
            'heading' => null, // null uses translation key
            'sort' => 2,
            'polling_interval' => null, // e.g., '10s', '1m', null to disable
            'limit' => 10,
            'paginated' => false,
            'columns' => [
                'event' => true,
                'causer' => true,
                'causer_limit' => 30,
                'subject_type' => true,
                'subject_type_limit' => 30,
                'description' => true,
                'description_limit' => 50,
                'created_at' => true,
            ],
        ],

        'stats' => [
            'risk_sample_size' => 500,
            'polling_interval' => null, // e.g., '10s', '1m', null to disable
        ],
    ],
    /*
    |--------------------------------------------------------------------------
    | Dashboard and Request Context
    |--------------------------------------------------------------------------
    |
    | Configure the dashboard and privacy-aware request context collectors.
    |
    */
    'dashboard' => [
        'enabled' => false,
        'title' => null, // null uses translation key
        'navigation_group' => null, // null uses resource group
        'navigation_sort' => 0,
        'navigation_icon' => 'heroicon-o-presentation-chart-bar',
    ],

    'auto_context' => [
        'enabled' => true,
        'capture_ip' => true,
        'anonymize_ip' => false,
        'capture_browser' => true,
        'capture_batch' => true,
        'capture_request' => true,
        'capture_tenant' => true,
        'collectors' => [
            RequestContextCollector::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Privacy & Compliance Settings
    |--------------------------------------------------------------------------
    |
    | Redaction is enabled by default so sensitive values do not leak through
    | tables, infolists, exports, timeline views, or diff previews.
    |
    */
    'privacy' => [
        'redacted_value' => '[redacted]',
        'redaction' => [
            'enabled' => true,
            'fields' => [
                'password',
                'password_confirmation',
                'current_password',
                'new_password',
                'token',
                'api_token',
                'access_token',
                'refresh_token',
                'secret',
                'api_key',
                'private_key',
                'remember_token',
            ],
            'patterns' => [
                '/(^|_)(password|token|secret|key)$/',
            ],
        ],
        'immutable_mode' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Risk Settings
    |--------------------------------------------------------------------------
    |
    | Risk scoring helps teams notice destructive, security-sensitive, or
    | privacy-sensitive activity without replacing Spatie's logging layer.
    |
    */
    'risk' => [
        'enabled' => true,
        'resolver' => null,
        'events' => [
            'deleted' => 45,
            'force_deleted' => 70,
            'restored' => 20,
            'updated' => 10,
        ],
        'log_names' => [
            'security' => 35,
            'auth' => 25,
            'permissions' => 40,
            'roles' => 40,
        ],
        'fields' => [
            '/password/i' => 45,
            '/token|secret|api_key|private_key/i' => 50,
            '/role|permission/i' => 35,
            '/email|phone|address/i' => 15,
        ],
        'signals' => [
            'context' => ['ip_address'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tamper-Evident Integrity
    |--------------------------------------------------------------------------
    |
    | Each new activity receives an HMAC signature after it is persisted. This
    | detects row modification; it does not prevent database-level deletion.
    |
    */
    'integrity' => [
        'enabled' => true,
        'key' => Env::get('ACTIVITY_LOG_INTEGRITY_KEY'),
    ],

    'retention' => [
        'enabled' => true,
        'log_activity' => true,
    ],

    'alerts' => [
        'enabled' => false,
        'high_risk_threshold' => 55,
        'rules' => [
            HighRiskActivityRule::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Causer Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the causer (the user who performed the activity).
    | You can define which attribute on the user model should be used as their display name.
    |
    */
    'causer' => [
        'display_attribute' => 'name',
    ],

    /*
    |--------------------------------------------------------------------------
    | Subject Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the subject (the record an activity was performed on).
    | When a subject does not implement HasActivityLogTitle, the first of these
    | attributes that exists on the model is used as its display title.
    |
    */
    'subject' => [
        'title_attributes' => ['name', 'title', 'email', 'username', 'label'],
    ],
];
