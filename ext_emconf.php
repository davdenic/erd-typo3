<?php

$EM_CONF['erd'] = [
    'title' => 'ERD Generator',
    'description' => 'Generate ER diagrams from TYPO3 TCA — backend module and CLI command',
    'category' => 'module',
    'author' => 'David Denicolo',
    'author_email' => '',
    'state' => 'stable',
    'version' => '4.6.0',
    'constraints' => [
        'depends' => [
            'typo3' => '14.0.0-14.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
