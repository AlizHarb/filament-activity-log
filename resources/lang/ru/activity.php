<?php

return [
    'label' => 'Журнал активности',
    'plural_label' => 'Журналы активности',
    'table' => [
        'column' => [
            'log_name' => 'Название журнала',
            'event' => 'Событие',
            'subject_id' => 'ID Субъекта',
            'subject_type' => 'Тип Субъекта',
            'causer_id' => 'ID Инициатора',
            'causer_type' => 'Тип Инициатора',
            'properties' => 'Свойства',
            'created_at' => 'Создано',
            'updated_at' => 'Обновлено',
            'description' => 'Описание',
            'subject' => 'Субъект',
            'causer' => 'Инициатор',
            'ip_address' => 'IP адрес',
            'browser' => 'Браузер',
        ],
        'filter' => [
            'event' => 'Событие',
            'created_at' => 'Создано',
            'created_from' => 'Создано с',
            'created_until' => 'Создано по',
            'causer' => 'Инициатор',
            'subject_type' => 'Тип Субъекта',
        ],
    ],
    'infolist' => [
        'section' => [
            'activity_details' => 'Детали активности',
        ],
        'tab' => [
            'overview' => 'Обзор',
            'changes' => 'Изменения',
            'raw_data' => 'Исходные данные',
            'old' => 'Старое',
            'new' => 'Новое',
        ],
        'entry' => [
            'log_name' => 'Название журнала',
            'event' => 'Событие',
            'created_at' => 'Создано',
            'description' => 'Описание',
            'subject' => 'Субъект',
            'causer' => 'Инициатор',
            'ip_address' => 'IP адрес',
            'browser' => 'Браузер',
            'attributes' => 'Атрибуты',
            'old' => 'Старое',
            'key' => 'Ключ',
            'value' => 'Значение',
            'properties' => 'Свойства',
        ],
    ],
    'action' => [
        'timeline' => [
            'label' => 'Хронология',
            'empty_state_title' => 'Журналы активности не найдены',
            'empty_state_description' => 'Для этой записи пока нет зарегистрированных действий.',
        ],
        'delete' => [
            'confirmation' => 'Вы уверены, что хотите удалить эту запись журнала активности? Это действие нельзя отменить.',
            'heading' => 'Удалить запись журнала',
            'button' => 'Удалить',
        ],
        'revert' => [
            'heading' => 'Отменить изменения',
            'confirmation' => 'Вы уверены, что хотите отменить это изменение? Это восстановит старые значения.',
            'button' => 'Отменить',
            'success' => 'Изменения успешно отменены',
            'no_old_data' => 'Нет старых данных для восстановления',
            'subject_not_found' => 'Модель субъекта не найдена',
        ],
        'export' => [
            'filename' => 'журналы_активности',
            'notification' => [
                'completed' => 'Экспорт журнала активности завершен, экспортировано :successful_rows :rows_label.',
            ],
        ],
    ],
    'filters' => 'Фильтры',
    'widgets' => [
        'latest_activity' => 'Последняя активность',
    ],
];
