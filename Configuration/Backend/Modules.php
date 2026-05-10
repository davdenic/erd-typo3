<?php

use Denic\Erd\Controller\ErdController;

return [
    'system_erd' => [
        'parent' => 'system',
        'access' => 'admin',
        'path' => '/module/system/erd',
        'navigationComponent' => '',
        'icon' => 'EXT:erd/Resources/Public/Icons/be_module.svg',
        'labels' => 'LLL:EXT:erd/Resources/Private/Language/locallang.xlf',
        'extensionName' => 'Erd',
        'controllerActions' => [
            ErdController::class => ['index', 'generate', 'download'],
        ],
    ],
];
