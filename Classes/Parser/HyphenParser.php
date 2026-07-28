<?php

declare(strict_types=1);
namespace StraschekIo\Hyphenator\Parser;

class HyphenParser
{
    public function replace(
        array $terms,
        string $content
    ): string {
        $replacements = [];
        $quotedTerms = [];
        foreach ($terms as $term) {
            $from = (string)$term['from'];
            if ($from === '') {
                continue;
            }
            $replacements[$from] = str_replace('|', '&shy;', strip_tags($term['to']));
            $quotedTerms[] = preg_quote($from, '/');
        }
        if ($quotedTerms === []) {
            return $content;
        }

        // longest first so overlapping terms prefer the longest match
        usort($quotedTerms, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));

        // Single pass over the raw HTML. The first (case-insensitive) branch consumes
        // everything that must never be touched — head/script/style/textarea blocks,
        // comments, tags including their attributes — so the term branch can only
        // match inside visible text. The term branch requires a leading non-word
        // character but no trailing boundary: prefix matching ("Arbeit" also hits
        // "Arbeitsamt") is intended behavior for German compounds.
        $pattern = '/(?i:'
            . '<(?:head|script|style|textarea)\b[^>]*+>.*?<\/(?:head|script|style|textarea)\s*+>'
            . '|<!--.*?-->'
            . '|<[^>]*+>'
            . ')'
            . '|(?<![\pL\pN])(?:' . implode('|', $quotedTerms) . ')/su';

        $result = preg_replace_callback(
            $pattern,
            static fn (array $matches): string => $matches[0][0] === '<'
                ? $matches[0]
                : ($replacements[$matches[0]] ?? $matches[0]),
            $content
        );

        return $result ?? $content;
    }
}
