<?php

declare(strict_types=1);
namespace StraschekIo\Hyphenator\Tests\Unit\Middleware;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use StraschekIo\Hyphenator\Middleware\HyphenatorMiddleware;
use StraschekIo\Hyphenator\Parser\HyphenParser;
use StraschekIo\Hyphenator\Repository\TermRepository;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\ServerRequest;

#[CoversClass(HyphenatorMiddleware::class)]
final class HyphenatorMiddlewareTest extends TestCase
{
    private const TERMS = [
        ['from' => 'Donaudampfschifffahrt', 'to' => 'Donau|dampf|schiff|fahrt'],
    ];

    public function testPassesResponseThroughWhenRepositoryReturnsNull(): void
    {
        $response = $this->createHtmlResponse('<p>Donaudampfschifffahrt</p>');
        $middleware = $this->createMiddleware(null);

        $result = $middleware->process(new ServerRequest(), $this->createHandler($response));

        self::assertSame($response, $result);
    }

    public function testIgnoresNonHtmlResponses(): void
    {
        $response = new Response();
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write('{"title":"Donaudampfschifffahrt"}');

        $parser = $this->createMock(HyphenParser::class);
        $parser->expects(self::never())->method('replace');
        $termRepository = $this->createMock(TermRepository::class);
        $termRepository->method('fetchAll')->willReturn(self::TERMS);
        $middleware = new HyphenatorMiddleware($parser, $termRepository);

        $result = $middleware->process(new ServerRequest(), $this->createHandler($response));

        self::assertSame($response, $result);
        self::assertSame('{"title":"Donaudampfschifffahrt"}', (string)$result->getBody());
    }

    public function testReturnsOriginalResponseWhenNothingMatches(): void
    {
        $response = $this->createHtmlResponse('<p>Hier matcht gar nichts.</p>');
        $middleware = $this->createMiddleware(self::TERMS);

        $result = $middleware->process(new ServerRequest(), $this->createHandler($response));

        self::assertSame($response, $result);
    }

    public function testReplacesBodyAndRemovesContentLengthHeader(): void
    {
        $response = $this->createHtmlResponse('<p>Die Donaudampfschifffahrt</p>');
        $response = $response->withHeader('Content-Length', '32');
        $middleware = $this->createMiddleware(self::TERMS);

        $result = $middleware->process(new ServerRequest(), $this->createHandler($response));

        self::assertSame('<p>Die Donau&shy;dampf&shy;schiff&shy;fahrt</p>', (string)$result->getBody());
        self::assertFalse($result->hasHeader('Content-Length'));
    }

    private function createMiddleware(?array $terms): HyphenatorMiddleware
    {
        $termRepository = $this->createMock(TermRepository::class);
        $termRepository->method('fetchAll')->willReturn($terms);

        return new HyphenatorMiddleware(new HyphenParser(), $termRepository);
    }

    private function createHandler(ResponseInterface $response): RequestHandlerInterface
    {
        return new class($response) implements RequestHandlerInterface {
            public function __construct(private readonly ResponseInterface $response)
            {
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->response;
            }
        };
    }

    private function createHtmlResponse(string $body): ResponseInterface
    {
        $response = new Response();
        $response = $response->withHeader('Content-Type', 'text/html; charset=utf-8');
        $response->getBody()->write($body);

        return $response;
    }
}
