<?php

return [
    'label' => 'יומן פעילות',
    'plural_label' => 'יומני פעילות',
    'table' => [
        'column' => [
            'log_name' => 'שם היומן',
            'event' => 'אירוע',
            'subject_id' => 'מזהה נושא',
            'subject_type' => 'סוג נושא',
            'causer_id' => 'מזהה גורם',
            'causer_type' => 'סוג גורם',
            'properties' => 'מאפיינים',
            'created_at' => 'נוצר ב',
            'updated_at' => 'עודכן ב',
            'description' => 'תיאור',
            'subject' => 'נושא',
            'causer' => 'גורם',
        ],
        'filter' => [
            'event' => 'אירוע',
            'created_at' => 'נוצר ב',
            'created_from' => 'נוצר מ',
            'created_until' => 'נוצר עד',
            'causer' => 'גורם',
            'subject_type' => 'סוג נושא',
        ],
    ],
    'infolist' => [
        'section' => [
            'activity_details' => 'פרטי פעילות',
        ],
        'tab' => [
            'overview' => 'סקירה כללית',
            'changes' => 'שינויים',
            'raw_data' => 'נתונים גולמיים',
            'old' => 'ישן',
            'new' => 'חדש',
        ],
        'entry' => [
            'log_name' => 'שם היומן',
            'event' => 'אירוע',
            'created_at' => 'נוצר ב',
            'description' => 'תיאור',
            'subject' => 'נושא',
            'causer' => 'גורם',
            'ip_address' => 'כתובת IP',
            'browser' => 'דפדפן',
            'attributes' => 'תכונות',
            'old' => 'ישן',
            'key' => 'מפתח',
            'value' => 'ערך',
            'properties' => 'מאפיינים',
        ],
    ],
    'action' => [
        'timeline' => [
            'label' => 'ציר זמן',
            'empty_state_title' => 'לא נמצאו יומני פעילות',
            'empty_state_description' => 'אין עדיין פעילויות מתועדות עבור רשומה זו.',
        ],
        'delete' => [
            'confirmation' => 'האם אתה בטוח שברצונך למחוק יומן פעילות זה? פעולה זו אינה הפיכה.',
            'heading' => 'מחק יומן פעילות',
            'button' => 'מחק',
        ],
        'revert' => [
            'heading' => 'בטל שינויים',
            'confirmation' => 'האם אתה בטוח שברצונך לבטל שינוי זה? פעולה זו תשחזר את הערכים הישנים.',
            'button' => 'בטל',
            'success' => 'השינויים בוטלו בהצלחה',
            'no_old_data' => 'אין נתונים ישנים זמינים לביטול',
            'subject_not_found' => 'מודל הנושא לא נמצא',
        ],
        'export' => [
            'filename' => 'יומני_פעילות',
            'notification' => [
                'completed' => 'ייצוא יומן הפעילות שלך הושלם ו-:successful_rows :rows_label יוצאו.',
            ],
        ],
    ],
    'filters' => 'מסננים',
    'widgets' => [
        'latest_activity' => 'פעילות אחרונה',
    ],
];
