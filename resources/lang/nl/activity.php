<?php

return [
    'label' => 'Activiteitenlogboek',
    'plural_label' => 'Activiteitenlogboeken',
    'table' => [
        'column' => [
            'log_name' => 'Logboeknaam',
            'event' => 'Gebeurtenis',
            'subject_id' => 'Onderwerp ID',
            'subject_type' => 'Onderwerp Type',
            'causer_id' => 'Veroorzaker ID',
            'causer_type' => 'Veroorzaker Type',
            'properties' => 'Eigenschappen',
            'created_at' => 'Aangemaakt Op',
            'updated_at' => 'Bijgewerkt Op',
            'description' => 'Beschrijving',
            'subject' => 'Onderwerp',
            'causer' => 'Veroorzaker',
            'ip_address' => 'IP Adres',
            'browser' => 'Browser',
        ],
        'filter' => [
            'event' => 'Gebeurtenis',
            'created_at' => 'Aangemaakt Op',
            'created_from' => 'Aangemaakt Van',
            'created_until' => 'Aangemaakt Tot',
            'causer' => 'Veroorzaker',
            'subject_type' => 'Onderwerp Type',
        ],
    ],
    'infolist' => [
        'section' => [
            'activity_details' => 'Activiteit Details',
        ],
        'tab' => [
            'overview' => 'Overzicht',
            'changes' => 'Wijzigingen',
            'raw_data' => 'Ruwe Data',
            'old' => 'Oud',
            'new' => 'Nieuw',
        ],
        'entry' => [
            'log_name' => 'Logboeknaam',
            'event' => 'Gebeurtenis',
            'created_at' => 'Aangemaakt Op',
            'description' => 'Beschrijving',
            'subject' => 'Onderwerp',
            'causer' => 'Veroorzaker',
            'ip_address' => 'IP Adres',
            'browser' => 'Browser',
            'attributes' => 'Attributen',
            'old' => 'Oud',
            'key' => 'Sleutel',
            'value' => 'Waarde',
            'properties' => 'Eigenschappen',
        ],
    ],
    'action' => [
        'timeline' => [
            'label' => 'Tijdlijn',
            'empty_state_title' => 'Geen activiteitenlogboeken gevonden',
            'empty_state_description' => 'Er zijn nog geen activiteiten geregistreerd voor dit item.',
        ],
        'delete' => [
            'confirmation' => 'Weet u zeker dat u dit activiteitenlogboek wilt verwijderen? Deze actie kan niet ongedaan worden gemaakt.',
            'heading' => 'Activiteitenlogboek Verwijderen',
            'button' => 'Verwijderen',
        ],
        'revert' => [
            'heading' => 'Wijzigingen Ongedaan Maken',
            'confirmation' => 'Weet u zeker dat u deze wijziging ongedaan wilt maken? Hiermee worden de oude waarden hersteld.',
            'button' => 'Ongedaan Maken',
            'success' => 'Wijzigingen succesvol ongedaan gemaakt',
            'no_old_data' => 'Geen oude gegevens beschikbaar om te herstellen',
            'subject_not_found' => 'Onderwerp model niet gevonden',
        ],
        'export' => [
            'filename' => 'activiteitenlogboeken',
            'notification' => [
                'completed' => 'Uw export van het activiteitenlogboek is voltooid en :successful_rows :rows_label zijn geëxporteerd.',
            ],
        ],
    ],
    'filters' => 'Filters',
    'widgets' => [
        'latest_activity' => 'Laatste Activiteit',
    ],
];
