<?php
defined('TYPO3_MODE') || die();

$_EXTKEY = 'ok_cookiebot';

// Register backend module
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
    'OkCookiebot',
    'web', // Main module: 'web'
    'cookieBot', // Submodule key
    null, // Position
    [
        'OliverKroener\\OkCookiebotCookieConsent\\Controller\\ConsentController' => 'index, error, save',
    ],
    [
        'access' => 'user,group',
        'icon'   => 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/module-icon.svg',
        'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xlf',
    ]
);

// Add TypoScript setup
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(
    '@import "EXT:' . $_EXTKEY . '/Configuration/TypoScript/setup.typoscript"'
);