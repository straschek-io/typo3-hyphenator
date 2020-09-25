<?php

return [
    'frontend' => [
        'straschek-io/typo3-hyphenator' => [
            'target' => \StraschekIo\Hyphenator\Middleware\HyphenatorMiddleware::class,
            'description' => '',
            'after' => [
                'typo3/cms-frontend/prepare-tsfe-rendering',
            ],
        ],
    ],
];
