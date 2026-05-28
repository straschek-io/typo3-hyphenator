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
    /**
     * @var TermRepository
     */
    private $termRepository;

    /**
     * @var HyphenParser
     */
    private $parser;

    public function __construct(HyphenParser $parser, TermRepository $termRepository)
    {
        $this->termRepository = $termRepository;
        $this->parser = $parser;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if ($terms = $this->termRepository->fetchAll()) {
            $output = $response->getBody()->__toString();
            $parsedOutput = $this->parser->replace($terms, $output);
            $stream = (new StreamFactory())->createStream($parsedOutput);
            $response = $response
                ->withBody($stream)
                ->withoutHeader('Content-Length');
        }
        return $response;
    }
}
