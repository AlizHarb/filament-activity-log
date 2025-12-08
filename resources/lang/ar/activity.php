<?php

return [
    'label' => 'سجل النشاط',
    'plural_label' => 'سجلات النشاط',
    'table' => [
        'column' => [
            'log_name' => 'اسم السجل',
            'event' => 'الحدث',
            'subject_id' => 'معرف الموضوع',
            'subject_type' => 'نوع الموضوع',
            'causer_id' => 'معرف المتسبب',
            'causer_type' => 'نوع المتسبب',
            'properties' => 'الخصائص',
            'created_at' => 'تاريخ الإنشاء',
            'updated_at' => 'تاريخ التحديث',
            'description' => 'الوصف',
            'subject' => 'الموضوع',
            'causer' => 'المتسبب',
        ],
        'filter' => [
            'event' => 'الحدث',
            'created_at' => 'تاريخ الإنشاء',
            'created_from' => 'من تاريخ',
            'created_until' => 'إلى تاريخ',
            'causer' => 'المتسبب',
            'subject_type' => 'نوع الموضوع',
        ],
    ],
    'infolist' => [
        'section' => [
            'activity_details' => 'تفاصيل النشاط',
        ],
        'tab' => [
            'overview' => 'نظرة عامة',
            'changes' => 'التغييرات',
            'raw_data' => 'البيانات الخام',
            'old' => 'القديم',
            'new' => 'الجديد',
        ],
        'entry' => [
            'log_name' => 'اسم السجل',
            'event' => 'الحدث',
            'created_at' => 'تاريخ الإنشاء',
            'description' => 'الوصف',
            'subject' => 'الموضوع',
            'causer' => 'المتسبب',
            'ip_address' => 'عنوان IP',
            'browser' => 'المتصفح',
            'attributes' => 'السمات',
            'old' => 'القديم',
            'key' => 'المفتاح',
            'value' => 'القيمة',
            'properties' => 'الخصائص',
        ],
    ],
    'action' => [
        'timeline' => [
            'label' => 'الجدول الزمني',
            'empty_state_title' => 'لم يتم العثور على سجلات نشاط',
            'empty_state_description' => 'لا توجد أنشطة مسجلة لهذا السجل حتى الآن.',
        ],
        'delete' => [
            'confirmation' => 'هل أنت متأكد أنك تريد حذف سجل النشاط هذا؟ لا يمكن التراجع عن هذا الإجراء.',
            'heading' => 'حذف سجل النشاط',
            'button' => 'حذف',
        ],
        'revert' => [
            'heading' => 'التراجع عن التغييرات',
            'confirmation' => 'هل أنت متأكد أنك تريد التراجع عن هذا التغيير؟ سيؤدي هذا إلى استعادة القيم القديمة.',
            'button' => 'تراجع',
            'success' => 'تم التراجع عن التغييرات بنجاح',
            'no_old_data' => 'لا توجد بيانات قديمة متاحة للتراجع',
            'subject_not_found' => 'لم يتم العثور على نموذج الموضوع',
        ],
        'export' => [
            'filename' => 'سجلات_النشاط',
            'notification' => [
                'completed' => 'اكتمل تصدير سجل النشاط وتم تصدير :successful_rows :rows_label.',
            ],
        ],
    ],
    'filters' => 'تصفية',
    'widgets' => [
        'latest_activity' => 'النشاط الأخير',
    ],
];
