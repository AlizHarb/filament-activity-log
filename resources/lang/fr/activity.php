<?php

return [
    'label' => 'Journal d\'activité',
    'plural_label' => 'Journaux d\'activité',
    'table' => [
        'column' => [
            'log_name' => 'Nom du journal',
            'event' => 'Événement',
            'subject_id' => 'ID du sujet',
            'subject_type' => 'Type de sujet',
            'causer_id' => 'ID de cause',
            'causer_type' => 'Type de cause',
            'properties' => 'Propriétés',
            'created_at' => 'Créé le',
            'updated_at' => 'Mis à jour le',
            'description' => 'Description',
            'subject' => 'Sujet',
            'causer' => 'Cause',
        ],
        'filter' => [
            'event' => 'Événement',
            'created_at' => 'Créé le',
            'created_from' => 'Créé depuis',
            'created_until' => 'Créé jusqu\'à',
            'causer' => 'Cause',
            'subject_type' => 'Type de sujet',
        ],
    ],
    'infolist' => [
        'section' => [
            'activity_details' => 'Détails de l\'activité',
        ],
        'tab' => [
            'overview' => 'Aperçu',
            'changes' => 'Changements',
            'raw_data' => 'Données brutes',
        ],
        'entry' => [
            'log_name' => 'Nom du journal',
            'event' => 'Événement',
            'created_at' => 'Créé le',
            'description' => 'Description',
            'subject' => 'Sujet',
            'causer' => 'Cause',
            'attributes' => 'Attributs',
            'old' => 'Ancien',
            'key' => 'Clé',
            'value' => 'Valeur',
            'properties' => 'Propriétés',
        ],
    ],
    'action' => [
        'timeline' => 'Chronologie',
        'delete' => [
            'confirmation' => 'Êtes-vous sûr de vouloir supprimer ce journal d\'activité ? Cette action est irréversible.',
            'heading' => 'Supprimer le journal d\'activité',
            'button' => 'Supprimer',
        ],
        'revert' => [
            'heading' => 'Annuler les modifications',
            'confirmation' => 'Êtes-vous sûr de vouloir annuler cette modification ? Cela restaurera les anciennes valeurs.',
            'button' => 'Annuler',
            'success' => 'Modifications annulées avec succès',
            'no_old_data' => 'Aucune ancienne donnée disponible pour annuler',
            'subject_not_found' => 'Modèle de sujet introuvable',
        ],
    ],
    'filters' => 'Filtres',
    'widgets' => [
        'latest_activity' => 'Activité récente',
    ],
];
