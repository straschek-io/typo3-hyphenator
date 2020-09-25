<?php

return [
    'ctrl' => [
        'label' => 'from',
        'default_sortby' => 'ORDER BY from',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'title' => 'LLL:EXT:hyphenator/Resources/Private/Language/locallang_tca.tx_hyphenator_term.xlf:tx_hyphenator_term',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'iconfile' => 'EXT:hyphenator/Resources/Public/Backend/Icons/tx_hyphenator_term.svg',
        'searchFields' => 'from',
    ],
    'interface' => [
        'showRecordFieldList' => 'hidden, from, to',
    ],
    'columns' => [
        'hidden' => [
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
            ],
        ],
        'from' => [
            'label' => 'LLL:EXT:hyphenator/Resources/Private/Language/locallang_tca.tx_hyphenator_term.xlf:from.label',
            'description' => 'LLL:EXT:hyphenator/Resources/Private/Language/locallang_tca.tx_hyphenator_term.xlf:from.description',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'eval' => 'trim,required',
                'max' => 255,
            ],
        ],
        'to' => [
            'label' => 'LLL:EXT:hyphenator/Resources/Private/Language/locallang_tca.tx_hyphenator_term.xlf:to.label',
            'description' => 'LLL:EXT:hyphenator/Resources/Private/Language/locallang_tca.tx_hyphenator_term.xlf:to.description',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'eval' => 'trim,required,StraschekIo\\Hyphenator\\Evaluation\\PipeEvaluation',
                'max' => 255,
            ],
        ],
    ],
    'types' => [
        '0' => [
            'showitem' => 'hidden, --palette--;;term',
        ],
    ],
    'palettes' => [
        'term' => [
            'showitem' => 'from, to',
        ],
    ],
];
