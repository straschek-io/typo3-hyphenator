<?php

declare(strict_types=1);
namespace StraschekIo\Hyphenator\Parser;

class HyphenParser
{
    public function replace(
        array $terms,
        string $content
    ) {
        foreach ($terms as $term) {
            $termReplacement = str_replace('|', '&shy;', strip_tags($term['to']));
            // super simple: super fast: https://regex101.com/r/SaXo0A/1
            $content = preg_replace('/(?<=[\>.*\s])' . $term['from'] . '(?!.*\s\<\/head\>)/s', $termReplacement, $content);
        }

        return $content;
    }
}
