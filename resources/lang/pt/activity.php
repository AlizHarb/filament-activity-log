<?php

return [
    'label' => 'Registro de Atividade',
    'plural_label' => 'Registros de Atividade',
    'table' => [
        'column' => [
            'log_name' => 'Nome do Registro',
            'event' => 'Evento',
            'subject_id' => 'ID do Assunto',
            'subject_type' => 'Tipo de Assunto',
            'causer_id' => 'ID do Causador',
            'causer_type' => 'Tipo de Causador',
            'properties' => 'Propriedades',
            'created_at' => 'Criado em',
            'updated_at' => 'Atualizado em',
            'description' => 'Descrição',
            'subject' => 'Assunto',
            'causer' => 'Causador',
        ],
        'filter' => [
            'event' => 'Evento',
            'created_at' => 'Criado em',
            'created_from' => 'Criado a partir de',
            'created_until' => 'Criado até',
            'causer' => 'Causador',
            'subject_type' => 'Tipo de Assunto',
        ],
    ],
    'infolist' => [
        'section' => [
            'activity_details' => 'Detalhes da Atividade',
        ],
        'tab' => [
            'overview' => 'Visão Geral',
            'changes' => 'Alterações',
            'raw_data' => 'Dados Brutos',
            'old' => 'Antigo',
            'new' => 'Novo',
        ],
        'entry' => [
            'log_name' => 'Nome do Registro',
            'event' => 'Evento',
            'created_at' => 'Criado em',
            'description' => 'Descrição',
            'subject' => 'Assunto',
            'causer' => 'Causador',
            'ip_address' => 'Endereço IP',
            'browser' => 'Navegador',
            'attributes' => 'Atributos',
            'old' => 'Antigo',
            'key' => 'Chave',
            'value' => 'Valor',
            'properties' => 'Propriedades',
        ],
    ],
    'action' => [
        'timeline' => [
            'label' => 'Linha do Tempo',
            'empty_state_title' => 'Nenhum registro de atividade encontrado',
            'empty_state_description' => 'Ainda não há atividades registradas para este registro.',
        ],
        'delete' => [
            'confirmation' => 'Tem certeza de que deseja excluir este registro de atividade? Esta ação não pode ser desfeita.',
            'heading' => 'Excluir Registro de Atividade',
            'button' => 'Excluir',
        ],
        'revert' => [
            'heading' => 'Reverter Alterações',
            'confirmation' => 'Tem certeza de que deseja reverter esta alteração? Isso restaurará os valores antigos.',
            'button' => 'Reverter',
            'success' => 'Alterações revertidas com sucesso',
            'no_old_data' => 'Nenhum dado antigo disponível para reverter',
            'subject_not_found' => 'Modelo de assunto não encontrado',
        ],
        'export' => [
            'filename' => 'registros_de_atividade',
            'notification' => [
                'completed' => 'A exportação do seu registro de atividade foi concluída e :successful_rows :rows_label foram exportadas.',
            ],
        ],
    ],
    'filters' => 'Filtros',
    'widgets' => [
        'latest_activity' => 'Atividade Recente',
    ],
];
