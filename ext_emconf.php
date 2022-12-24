<?php

$EM_CONF['erd'] = [
    'title' => 'ERD Generator',
    'description' => 'Generate ER diagrams from TYPO3 TCA — backend module and CLI command',
    'category' => 'module',
    'author' => 'David Denicolo',
    'author_email' => '',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-11.5.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
