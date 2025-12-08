<?php

return [
    'label' => 'Activity Log',
    'plural_label' => 'Activity Logs',
    'table' => [
        'column' => [
            'log_name' => 'Log Name',
            'event' => 'Event',
            'subject_id' => 'Subject ID',
            'subject_type' => 'Subject Type',
            'causer_id' => 'Causer ID',
            'causer_type' => 'Causer Type',
            'properties' => 'Properties',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'description' => 'Description',
            'subject' => 'Subject',
            'causer' => 'Causer',
            'ip_address' => 'IP Address',
            'browser' => 'Browser',
        ],
        'filter' => [
            'event' => 'Event',
            'created_at' => 'Created At',
            'created_from' => 'Created From',
            'created_until' => 'Created Until',
            'causer' => 'Causer',
            'subject_type' => 'Subject Type',
        ],
    ],
    'infolist' => [
        'section' => [
            'activity_details' => 'Activity Details',
        ],
        'tab' => [
            'overview' => 'Overview',
            'changes' => 'Changes',
            'raw_data' => 'Raw Data',
            'old' => 'Old',
            'new' => 'New',
        ],
        'entry' => [
            'log_name' => 'Log Name',
            'event' => 'Event',
            'created_at' => 'Created At',
            'description' => 'Description',
            'subject' => 'Subject',
            'causer' => 'Causer',
            'ip_address' => 'IP Address',
            'browser' => 'Browser',
            'attributes' => 'Attributes',
            'old' => 'Old',
            'key' => 'Key',
            'value' => 'Value',
            'properties' => 'Properties',
        ],
    ],
    'action' => [
        'timeline' => [
            'label' => 'Timeline',
            'empty_state_title' => 'No activity logs found',
            'empty_state_description' => 'There are no activities recorded for this record yet.',
        ],
        'delete' => [
            'confirmation' => 'Are you sure you want to delete this activity log? This action cannot be undone.',
            'heading' => 'Delete Activity Log',
            'button' => 'Delete',
        ],
        'revert' => [
            'heading' => 'Revert Changes',
            'confirmation' => 'Are you sure you want to revert this change? This will restore the old values.',
            'button' => 'Revert',
            'success' => 'Changes reverted successfully',
            'no_old_data' => 'No old data available to revert',
            'subject_not_found' => 'Subject model not found',
        ],
        'export' => [
            'filename' => 'activity_logs',
            'notification' => [
                'completed' => 'Your activity log export has completed and :successful_rows :rows_label exported.',
            ],
        ],
    ],
    'filters' => 'Filters',
    'widgets' => [
        'latest_activity' => 'Latest Activity',
    ],
];
