<?php

$EM_CONF['typo3_hyphenator'] = [
    'title' => 'Hyphenator',
    'description' => 'Provides soft-hyphen replacement in frontend via editable records',
    'category' => 'frontend',
    'author' => 'Michael Straschek',
    'author_email' => 'hallo@straschek.io',
    'state' => 'stable',
    'clearCacheOnLoad' => false,
    'version' => '1.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-10.99.99',
        ],
    ],
];
