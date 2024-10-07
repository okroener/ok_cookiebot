<?php

defined('TYPO3_MODE') || die('Access denied!');

$_EXTKEY = 'ok_cookiebot';

// Register backend module
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
    'OkCookiebot',
    'web', // Main module: 'web'
    'cookieBot', // Submodule key
    '', // Position
    [
        'OliverKroener\\OkCookiebotCookieConsent\\Controller\\ConsentController' => 'index, error, save',
    ],
    [
        'access' => 'user,group',
        'icon'   => 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/module-icon.svg',
        'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xlf',
    ]
);