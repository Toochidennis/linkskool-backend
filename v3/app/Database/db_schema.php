<?php

$schema = [
    'account_chart' => [
        'typeid' => [
            'type' => 'int(10) UNSIGNED',
            'nullable' => false,
            'auto_increment' => true,
            'primary' => true,
        ],
        'account_id' => [
            'type' => 'varchar(50)',
            'nullable' => true,
            'default' => null,
        ],
        'account_type' => [
            'type' => 'int(11)',
            'nullable' => true,
            'default' => null,
        ],
        'account_name' => [
            'type' => 'varchar(255)',
            'nullable' => true,
            'default' => null,
        ],
        'inactive' => [
            'type' => 'varchar(10)',
            'nullable' => true,
            'default' => null,
        ],
        '__meta' => [
            'engine' => 'InnoDB',
            'charset' => 'latin1',
            'collate' => 'latin1_swedish_ci',
        ],
    ],
];
