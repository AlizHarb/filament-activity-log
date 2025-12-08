<?php

return [
    'label' => '活动日志',
    'plural_label' => '活动日志',
    'table' => [
        'column' => [
            'log_name' => '日志名称',
            'event' => '事件',
            'subject_id' => '主体 ID',
            'subject_type' => '主体类型',
            'causer_id' => '致因 ID',
            'causer_type' => '致因类型',
            'properties' => '属性',
            'created_at' => '创建于',
            'updated_at' => '更新于',
            'description' => '描述',
            'subject' => '主体',
            'causer' => '致因',
            'ip_address' => 'IP 地址',
            'browser' => '浏览器',
        ],
        'filter' => [
            'event' => '事件',
            'created_at' => '创建于',
            'created_from' => '创建自',
            'created_until' => '创建至',
            'causer' => '致因',
            'subject_type' => '主体类型',
        ],
    ],
    'infolist' => [
        'section' => [
            'activity_details' => '活动详情',
        ],
        'tab' => [
            'overview' => '概览',
            'changes' => '变更',
            'raw_data' => '原始数据',
            'old' => '旧值',
            'new' => '新值',
        ],
        'entry' => [
            'log_name' => '日志名称',
            'event' => '事件',
            'created_at' => '创建于',
            'description' => '描述',
            'subject' => '主体',
            'causer' => '致因',
            'ip_address' => 'IP 地址',
            'browser' => '浏览器',
            'attributes' => '属性',
            'old' => '旧值',
            'key' => '键',
            'value' => '值',
            'properties' => '属性',
        ],
    ],
    'action' => [
        'timeline' => [
            'label' => '时间轴',
            'empty_state_title' => '未找到活动日志',
            'empty_state_description' => '此记录尚无活动日志。',
        ],
        'delete' => [
            'confirmation' => '您确定要删除此活动日志吗？此操作无法撤销。',
            'heading' => '删除活动日志',
            'button' => '删除',
        ],
        'revert' => [
            'heading' => '撤销更改',
            'confirmation' => '您确定要撤销此更改吗？这将恢复旧值。',
            'button' => '撤销',
            'success' => '更改已成功撤销',
            'no_old_data' => '无旧数据可用于撤销',
            'subject_not_found' => '未找到主体模型',
        ],
        'export' => [
            'filename' => '活动日志',
            'notification' => [
                'completed' => '您的活动日志导出已完成，已导出 :successful_rows :rows_label。',
            ],
        ],
    ],
    'filters' => '筛选',
    'widgets' => [
        'latest_activity' => '最新活动',
    ],
];
