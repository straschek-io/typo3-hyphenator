<?php
defined('TYPO3_MODE') || die('Access denied.');

(static function () {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals'][\StraschekIo\Hyphenator\Evaluation\PipeEvaluation::class] = '';
})();
