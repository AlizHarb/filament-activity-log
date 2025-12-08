<?php

return [
    'label' => 'Registro de actividad',
    'plural_label' => 'Registros de actividad',
    'table' => [
        'column' => [
            'log_name' => 'Nombre del registro',
            'event' => 'Evento',
            'subject_id' => 'ID del sujeto',
            'subject_type' => 'Tipo de sujeto',
            'causer_id' => 'ID del causante',
            'causer_type' => 'Tipo de causante',
            'properties' => 'Propiedades',
            'created_at' => 'Creado el',
            'updated_at' => 'Actualizado el',
            'description' => 'Descripción',
            'subject' => 'Sujeto',
            'causer' => 'Causante',
        ],
        'filter' => [
            'event' => 'Evento',
            'created_at' => 'Creado el',
            'created_from' => 'Creado desde',
            'created_until' => 'Creado hasta',
            'causer' => 'Causante',
            'subject_type' => 'Tipo de sujeto',
        ],
    ],
    'infolist' => [
        'section' => [
            'activity_details' => 'Detalles de la actividad',
        ],
        'tab' => [
            'overview' => 'Resumen',
            'changes' => 'Cambios',
            'raw_data' => 'Datos brutos',
            'old' => 'Antiguo',
            'new' => 'Nuevo',
        ],
        'entry' => [
            'log_name' => 'Nombre del registro',
            'event' => 'Evento',
            'created_at' => 'Creado el',
            'description' => 'Descripción',
            'subject' => 'Sujeto',
            'causer' => 'Causante',
            'ip_address' => 'Dirección IP',
            'browser' => 'Navegador',
            'attributes' => 'Atributos',
            'old' => 'Antiguo',
            'key' => 'Clave',
            'value' => 'Valor',
            'properties' => 'Propiedades',
        ],
    ],
    'action' => [
        'timeline' => [
            'label' => 'Línea de tiempo',
            'empty_state_title' => 'No se encontraron registros de actividad',
            'empty_state_description' => 'No hay actividades registradas para este registro todavía.',
        ],
        'delete' => [
            'confirmation' => '¿Está seguro de que desea eliminar este registro de actividad? Esta acción no se puede deshacer.',
            'heading' => 'Eliminar registro de actividad',
            'button' => 'Eliminar',
        ],
        'revert' => [
            'heading' => 'Revertir cambios',
            'confirmation' => '¿Está seguro de que desea revertir este cambio? Esto restaurará los valores antiguos.',
            'button' => 'Revertir',
            'success' => 'Cambios revertidos exitosamente',
            'no_old_data' => 'No hay datos antiguos disponibles para revertir',
            'subject_not_found' => 'Modelo de sujeto no encontrado',
        ],
        'export' => [
            'filename' => 'registros_de_actividad',
            'notification' => [
                'completed' => 'La exportación de su registro de actividad se ha completado y se han exportado :successful_rows :rows_label.',
            ],
        ],
    ],
    'filters' => 'Filtros',
    'widgets' => [
        'latest_activity' => 'Actividad reciente',
    ],
];
