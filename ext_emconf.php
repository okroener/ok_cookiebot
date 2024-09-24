<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Cookiebot Cookie Consent',
    'description' => 'Provides a backend module to manage privacy cookie consent scripts for Cookiebot.',
    'category' => 'module',
    'author' => 'Oliver Kroener',
    'author_email' => 'ok@oliver-kroener.de',
    'state' => 'beta',
    'version' => '3.0.3',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-11.5.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];