<?php
defined('TYPO3_MODE') || die();

// Add new field to sys_template
/**\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_template', [
    'tx_ok_cookiebot' => [
        'exclude' => 1,
        'title' => 'LLL:EXT:ok_cookiebot/Resources/Private/Language/locallang_db.xlf:sys_template.ok_cookiebot_consent',
        'config' => [
            'type' => 'text',
            'rows' => 15,
            'cols' => 80,
            'eval' => 'trim',
            'softref' => 'typolink',
            'default' => '',
        ],
    ],
]);*/

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('ok_cookiebot', 'Configuration/TypoScript', '[kroener.DIGITAL] Cookiebot');