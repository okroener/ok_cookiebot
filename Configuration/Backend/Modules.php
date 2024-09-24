<?php

use OliverKroener\OkCookiebotCookieConsent\Controller\BackendController;

return [
    'web_cookieBot' => [
        'parent' => 'web', // Main module: 'web'
        'access' => 'user,group',
        'path' => '/modules/web/cookiebot',
        'iconIdentifier' => 'module-cookieBot', // Register this icon separately
        'labels' => 'LLL:EXT:ok_cookiebot/Resources/Private/Language/locallang.xlf',
        'extensionName' => 'OkCookiebot',
        'controllerActions' => [
            BackendController::class => [
                'index',
                'error',
                'save',
            ],
        ],
    ],
];
