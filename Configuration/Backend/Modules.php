<?php

use Denic\Erd\Controller\ErdController;

return [
    'web_erd' => [
        'parent' => 'web',
        'access' => 'admin',
        'path' => '/module/web/erd',
        'icon' => 'EXT:erd/Resources/Public/Icons/be_module.svg',
        'labels' => 'LLL:EXT:erd/Resources/Private/Language/locallang.xlf',
        'extensionName' => 'Erd',
        'controllerActions' => [
            ErdController::class => ['index', 'generate', 'download'],
        ],
    ],
];
