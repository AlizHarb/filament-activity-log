<?php

return [
    'label' => 'Aktivitätsprotokoll',
    'plural_label' => 'Aktivitätsprotokolle',
    'table' => [
        'column' => [
            'log_name' => 'Protokollname',
            'event' => 'Ereignis',
            'subject_id' => 'Subjekt ID',
            'subject_type' => 'Subjekt Typ',
            'causer_id' => 'Verursacher ID',
            'causer_type' => 'Verursacher Typ',
            'properties' => 'Eigenschaften',
            'created_at' => 'Erstellt am',
            'updated_at' => 'Aktualisiert am',
            'description' => 'Beschreibung',
            'subject' => 'Subjekt',
            'causer' => 'Verursacher',
            'ip_address' => 'IP Adresse',
            'browser' => 'Browser',
        ],
        'filter' => [
            'event' => 'Ereignis',
            'created_at' => 'Erstellt am',
            'created_from' => 'Erstellt von',
            'created_until' => 'Erstellt bis',
            'causer' => 'Verursacher',
            'subject_type' => 'Subjekt Typ',
        ],
    ],
    'infolist' => [
        'section' => [
            'activity_details' => 'Aktivitätsdetails',
        ],
        'tab' => [
            'overview' => 'Übersicht',
            'changes' => 'Änderungen',
            'raw_data' => 'Rohdaten',
            'old' => 'Alt',
            'new' => 'Neu',
        ],
        'entry' => [
            'log_name' => 'Protokollname',
            'event' => 'Ereignis',
            'created_at' => 'Erstellt am',
            'description' => 'Beschreibung',
            'subject' => 'Subjekt',
            'causer' => 'Verursacher',
            'ip_address' => 'IP Adresse',
            'browser' => 'Browser',
            'attributes' => 'Attribute',
            'old' => 'Alt',
            'key' => 'Schlüssel',
            'value' => 'Wert',
            'properties' => 'Eigenschaften',
        ],
    ],
    'action' => [
        'timeline' => [
            'label' => 'Zeitstrahl',
            'empty_state_title' => 'Keine Aktivitätsprotokolle gefunden',
            'empty_state_description' => 'Es wurden noch keine Aktivitäten für diesen Datensatz aufgezeichnet.',
        ],
        'delete' => [
            'confirmation' => 'Sind Sie sicher, dass Sie dieses Aktivitätsprotokoll löschen möchten? Diese Aktion kann nicht rückgängig gemacht werden.',
            'heading' => 'Aktivitätsprotokoll löschen',
            'button' => 'Löschen',
        ],
        'revert' => [
            'heading' => 'Änderungen rückgängig machen',
            'confirmation' => 'Sind Sie sicher, dass Sie diese Änderung rückgängig machen möchten? Dies wird die alten Werte wiederherstellen.',
            'button' => 'Rückgängig machen',
            'success' => 'Änderungen erfolgreich rückgängig gemacht',
            'no_old_data' => 'Keine alten Daten verfügbar zum Rückgängigmachen',
            'subject_not_found' => 'Subjekt-Modell nicht gefunden',
        ],
        'export' => [
            'filename' => 'aktivitaetsprotokolle',
            'notification' => [
                'completed' => 'Ihr Aktivitätsprotokoll-Export wurde abgeschlossen und :successful_rows :rows_label wurden exportiert.',
            ],
        ],
    ],
    'filters' => 'Filter',
    'widgets' => [
        'latest_activity' => 'Neueste Aktivität',
    ],
];
