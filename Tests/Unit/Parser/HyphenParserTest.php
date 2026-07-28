<?php

declare(strict_types=1);
namespace StraschekIo\Hyphenator\Tests\Unit\Parser;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StraschekIo\Hyphenator\Parser\HyphenParser;

#[CoversClass(HyphenParser::class)]
final class HyphenParserTest extends TestCase
{
    private const TERMS = [
        ['from' => 'Donaudampfschifffahrt', 'to' => 'Donau|dampf|schiff|fahrt'],
        ['from' => 'Straßenbahn', 'to' => 'Straßen|bahn'],
        ['from' => 'Arbeit', 'to' => 'Ar|beit'],
        ['from' => 'Arbeitsamt', 'to' => 'Ar|beits|amt'],
    ];

    private HyphenParser $parser;

    protected function setUp(): void
    {
        $this->parser = new HyphenParser();
    }

    public static function punctuationContextProvider(): array
    {
        return [
            'brackets' => ['<p>(Donaudampfschifffahrt)</p>', '<p>(Donau&shy;dampf&shy;schiff&shy;fahrt)</p>'],
            'double quotes' => ['<p>"Donaudampfschifffahrt"</p>', '<p>"Donau&shy;dampf&shy;schiff&shy;fahrt"</p>'],
            'guillemets' => ['<p>»Donaudampfschifffahrt«</p>', '<p>»Donau&shy;dampf&shy;schiff&shy;fahrt«</p>'],
            'dash' => ['<p>-Donaudampfschifffahrt</p>', '<p>-Donau&shy;dampf&shy;schiff&shy;fahrt</p>'],
            'start of text' => ['Donaudampfschifffahrt voraus', 'Donau&shy;dampf&shy;schiff&shy;fahrt voraus'],
        ];
    }

    public static function untouchedContextProvider(): array
    {
        return [
            'attributes' => ['<a href="/Donaudampfschifffahrt" title="Donaudampfschifffahrt">x</a>'],
            'head and title' => ['<head><title>Donaudampfschifffahrt</title></head>'],
            'script' => ['<script>var x = "Donaudampfschifffahrt";</script>'],
            'uppercase script' => ['<SCRIPT>console.log("Donaudampfschifffahrt");</SCRIPT>'],
            'style' => ['<style>.donaudampfschifffahrt { color: red; }</style>'],
            'textarea' => ['<textarea>Donaudampfschifffahrt Arbeit</textarea>'],
            'comment' => ['<!-- Donaudampfschifffahrt im Kommentar -->'],
        ];
    }

    #[DataProvider('punctuationContextProvider')]
    public function testReplacesTermsAdjacentToPunctuation(string $content, string $expected): void
    {
        self::assertSame($expected, $this->parser->replace(self::TERMS, $content));
    }

    #[DataProvider('untouchedContextProvider')]
    public function testLeavesNonTextContextsUntouched(string $content): void
    {
        self::assertSame($content, $this->parser->replace(self::TERMS, $content));
    }

    public function testReplacesTermInText(): void
    {
        self::assertSame(
            '<p>Die Donau&shy;dampf&shy;schiff&shy;fahrt fährt.</p>',
            $this->parser->replace(self::TERMS, '<p>Die Donaudampfschifffahrt fährt.</p>')
        );
    }

    public function testReplacesTermInsideSvgText(): void
    {
        self::assertSame(
            '<svg viewBox="0 0 10 10"><text>Ar&shy;beit</text></svg>',
            $this->parser->replace(self::TERMS, '<svg viewBox="0 0 10 10"><text>Arbeit</text></svg>')
        );
    }

    public function testMatchesTermAsPrefixOfLongerWords(): void
    {
        self::assertSame(
            '<p>Die Ar&shy;beitgeberin</p>',
            $this->parser->replace(self::TERMS, '<p>Die Arbeitgeberin</p>')
        );
    }

    public function testPrefersLongestMatchingTerm(): void
    {
        self::assertSame(
            '<p>Das Ar&shy;beits&shy;amt</p>',
            $this->parser->replace(self::TERMS, '<p>Das Arbeitsamt</p>')
        );
    }

    public function testRequiresLeadingWordBoundary(): void
    {
        self::assertSame(
            '<p>XDonaudampfschifffahrtX und Rüstungsarbeit</p>',
            $this->parser->replace(self::TERMS, '<p>XDonaudampfschifffahrtX und Rüstungsarbeit</p>')
        );
    }

    public function testKeepsUmlautsRaw(): void
    {
        self::assertSame(
            '<p>Die Straßen&shy;bahn</p>',
            $this->parser->replace(self::TERMS, '<p>Die Straßenbahn</p>')
        );
    }

    public function testNoOpOutputIsByteIdentical(): void
    {
        $content = '<p>Hier matcht gar nichts.</p>';
        self::assertSame($content, $this->parser->replace(self::TERMS, $content));
    }

    public function testEmptyTermListReturnsContentUnchanged(): void
    {
        $content = '<p>Die Donaudampfschifffahrt</p>';
        self::assertSame($content, $this->parser->replace([], $content));
    }

    public function testSkipsTermsWithEmptyFrom(): void
    {
        $content = '<p>Hallo Welt, gut.</p>';
        self::assertSame($content, $this->parser->replace([['from' => '', 'to' => 'X|Y']], $content));
    }

    public function testEmptyContentStaysEmpty(): void
    {
        self::assertSame('', $this->parser->replace(self::TERMS, ''));
    }

    public function testStripsTagsFromReplacement(): void
    {
        self::assertSame(
            '<p>Ar&shy;beit</p>',
            $this->parser->replace([['from' => 'Arbeit', 'to' => 'Ar|<b>beit</b>']], '<p>Arbeit</p>')
        );
    }

    public function testTreatsRegexMetacharactersInTermsLiterally(): void
    {
        self::assertSame(
            '<p>Der Preis&shy;(netto) und aXb</p>',
            $this->parser->replace(
                [
                    ['from' => 'Preis (netto)', 'to' => 'Preis|(netto)'],
                    ['from' => 'a.b', 'to' => 'a|.|b'],
                ],
                '<p>Der Preis (netto) und aXb</p>'
            )
        );
    }
}
