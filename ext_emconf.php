<?php

$EM_CONF['erd'] = [
    'title' => 'ERD Generator',
    'description' => 'Generate ER diagrams from TYPO3 TCA — backend module and CLI command',
    'category' => 'module',
    'author' => 'David Denicolo',
    'author_email' => '',
    'state' => 'stable',
    'version' => '3.4.1',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-13.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
