<?php
defined('TYPO3') || die();

$_EXTKEY = 'ok_cookiebot';

// Register TypoScript for the backend module
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
    'ok_cookiebot',
    'setup',
    'EXT:ok_cookiebot/Configuration/Defaults.typoscript'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'OkCookiebotCookieConsent',
    'Consent',
    [
        OliverKroener\OkCookiebotCookieConsent\Controller\BackendController::class => 'renderBannerScript'
    ]
);