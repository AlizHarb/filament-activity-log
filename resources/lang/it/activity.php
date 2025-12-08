<?php

return [
    'label' => 'Registro Attività',
    'plural_label' => 'Registri Attività',
    'table' => [
        'column' => [
            'log_name' => 'Nome Registro',
            'event' => 'Evento',
            'subject_id' => 'ID Soggetto',
            'subject_type' => 'Tipo Soggetto',
            'causer_id' => 'ID Autore',
            'causer_type' => 'Tipo Autore',
            'properties' => 'Proprietà',
            'created_at' => 'Creato Il',
            'updated_at' => 'Aggiornato Il',
            'description' => 'Descrizione',
            'subject' => 'Soggetto',
            'causer' => 'Autore',
            'ip_address' => 'Indirizzo IP',
            'browser' => 'Browser',
        ],
        'filter' => [
            'event' => 'Evento',
            'created_at' => 'Creato Il',
            'created_from' => 'Creato Dal',
            'created_until' => 'Creato Fino Al',
            'causer' => 'Autore',
            'subject_type' => 'Tipo Soggetto',
        ],
    ],
    'infolist' => [
        'section' => [
            'activity_details' => 'Dettagli Attività',
        ],
        'tab' => [
            'overview' => 'Panoramica',
            'changes' => 'Modifiche',
            'raw_data' => 'Dati Grezzi',
            'old' => 'Vecchio',
            'new' => 'Nuovo',
        ],
        'entry' => [
            'log_name' => 'Nome Registro',
            'event' => 'Evento',
            'created_at' => 'Creato Il',
            'description' => 'Descrizione',
            'subject' => 'Soggetto',
            'causer' => 'Autore',
            'ip_address' => 'Indirizzo IP',
            'browser' => 'Browser',
            'attributes' => 'Attributi',
            'old' => 'Vecchio',
            'key' => 'Chiave',
            'value' => 'Valore',
            'properties' => 'Proprietà',
        ],
    ],
    'action' => [
        'timeline' => [
            'label' => 'Cronologia',
            'empty_state_title' => 'Nessun registro attività trovato',
            'empty_state_description' => 'Non ci sono ancora attività registrate per questo record.',
        ],
        'delete' => [
            'confirmation' => 'Sei sicuro di voler eliminare questo registro attività? Questa azione non può essere annullata.',
            'heading' => 'Elimina Registro Attività',
            'button' => 'Elimina',
        ],
        'revert' => [
            'heading' => 'Ripristina Modifiche',
            'confirmation' => 'Sei sicuro di voler ripristinare questa modifica? Questo ripristinerà i vecchi valori.',
            'button' => 'Ripristina',
            'success' => 'Modifiche ripristinate con successo',
            'no_old_data' => 'Nessun dato vecchio disponibile per il ripristino',
            'subject_not_found' => 'Modello del soggetto non trovato',
        ],
        'export' => [
            'filename' => 'registri_attivita',
            'notification' => [
                'completed' => 'L\'esportazione del registro attività è stata completata e :successful_rows :rows_label sono state esportate.',
            ],
        ],
    ],
    'filters' => 'Filtri',
    'widgets' => [
        'latest_activity' => 'Attività Recente',
    ],
];
