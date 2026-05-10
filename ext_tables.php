<?php

use Denic\Erd\Controller\ErdController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3_MODE') || die();

(function () {
    ExtensionUtility::registerModule(
        'Erd',
        'system',
        'erd',
        '',
        [
            ErdController::class => 'index, generate, download',
        ],
        [
            'access' => 'admin',
            'icon' => 'EXT:erd/Resources/Public/Icons/be_module.svg',
            'labels' => 'LLL:EXT:erd/Resources/Private/Language/locallang.xlf',
            'navigationComponentId' => '',
        ]
    );
})();
