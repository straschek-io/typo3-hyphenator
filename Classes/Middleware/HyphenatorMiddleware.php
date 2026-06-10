<?php

declare(strict_types = 1);
namespace StraschekIo\Hyphenator\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use StraschekIo\Hyphenator\Parser\HyphenParser;
use StraschekIo\Hyphenator\Repository\TermRepository;
use TYPO3\CMS\Core\Http\StreamFactory;

final class HyphenatorMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly HyphenParser $parser,
        private readonly TermRepository $termRepository,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if (!$this->isHtmlResponse($response)) {
            return $response;
        }

        $terms = $this->termRepository->fetchAll();
        if ($terms === []) {
            return $response;
        }

        $body = (string) $response->getBody();
        $parsedBody = $this->parser->replace($terms, $body);

        if ($parsedBody === $body) {
            return $response;
        }

        $stream = (new StreamFactory())->createStream($parsedBody);

        return $response
            ->withBody($stream)
            ->withoutHeader('Content-Length');
    }

    private function isHtmlResponse(ResponseInterface $response): bool
    {
        return str_contains(
            strtolower($response->getHeaderLine('Content-Type')),
            'text/html'
        );
    }
}
