<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Resource Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the Activity Log resource.
    |
    */
    'resource' => [
        'class' => \AlizHarb\ActivityLog\Resources\ActivityLogs\ActivityLogResource::class,
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
            'default' => 50,
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
            'subject_type' => [
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
                'searchable' => true,
                'sortable' => true,
            ],
            'ip_address' => [
                'visible' => true,
                'searchable' => true,
            ],
            'user_agent' => [
                'visible' => true,
                'searchable' => true,
            ],
        ],
        'filters' => [
            'log_name' => true,
            'event' => true,
            'created_at' => true,
            'causer' => true,
            'subject_type' => true,
        ],
        'actions' => [
            'timeline' => true,
            'view' => true,
            'revert' => true,
            'restore' => true,
            'delete' => true,
            'export' => true,
        ],
        'bulk_actions' => [
            'delete' => true,
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
            'created_at' => true,
            'causer' => true,
            'subject' => true,
            'description' => true,
            'properties_attributes' => true,
            'properties_old' => true,
            'properties_raw' => true,
            'ip_address' => true,
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
        'show_action' => true,
        'icon' => 'heroicon-m-clock',
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
         * Custom authorization callback.
         *
         * This callback receives the authenticated user and should return a boolean.
         * If set, this takes precedence over the 'enabled' setting and permission checks.
         *
         * Example: fn($user) => $user->id === 1
         * Example: fn($user) => $user->hasRole('super_admin')
         * Example: 'App\Support\ActivityLogAuthorization' (class with __invoke method)
         */
        'custom_authorization' => null,

        'view_any' => 'view_any_activity',
        'view' => 'view_activity',
        'create' => 'create_activity',
        'update' => 'update_activity',
        'delete' => 'delete_activity',
        'restore' => 'restore_activity',
        'force_delete' => 'force_delete_activity',
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
            'class' => \AlizHarb\ActivityLog\Pages\UserActivitiesPage::class,
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
        'dashboard' => true,
        'widgets' => [
            \AlizHarb\ActivityLog\Widgets\ActivityChartWidget::class,
            \AlizHarb\ActivityLog\Widgets\LatestActivityWidget::class,
            \AlizHarb\ActivityLog\Widgets\ActivityHeatmapWidget::class,
            \AlizHarb\ActivityLog\Widgets\ActivityStatsWidget::class,
        ],

        /**
         * Activity Chart Widget Configuration
         */
        'activity_chart' => [
            'enabled' => true,
            'heading' => 'Activity Over Time',
            'sort' => 1,
            'max_height' => '300px',
            'polling_interval' => null, // e.g., '10s', '1m', null to disable
            'days' => 30,
            'type' => 'line', // 'line', 'bar', 'pie', 'doughnut', 'polarArea', 'radar'
            'label' => 'Activities',
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
    ],
    /*
    |--------------------------------------------------------------------------
    | Advanced Settings (v1.3.0)
    |--------------------------------------------------------------------------
    |
    | Configuration for new features in v1.3.0.
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
        'capture_browser' => true,
        'capture_batch' => true,
    ],
];
