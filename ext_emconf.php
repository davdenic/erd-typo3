<?php

$EM_CONF['erd'] = [
    'title' => 'ERD Generator',
    'description' => 'Generate ER diagrams from TYPO3 TCA — backend module and CLI command',
    'category' => 'module',
    'author' => 'David Denicolo',
    'author_email' => '',
    'state' => 'stable',
    'version' => '2.3.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-12.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
