<?php

namespace ZipkinGuzzle\Middleware;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zipkin\Kind;
use Zipkin\Tags;
use Zipkin\Tracing;
use ZipkinGuzzle\RequestHeaders;

/**
 * @param Tracing $tracing
 * @return HandlerStack
 */
function defaultHandlerStack(Tracing $tracing)
{
    $stack = HandlerStack::create();
    $stack->push(tracing($tracing));
    return $stack;
}

/**
 * @param Tracing $tracing
 * @return callable
 */
function tracing(Tracing $tracing)
{
    return function (callable $handler) use ($tracing) {
        return function (RequestInterface $request, array $options) use ($handler, $tracing) {
            $span = $tracing->getTracer()->nextSpan();
            $span->setName($request->getMethod());
            $span->setKind(Kind\CLIENT);
            $span->tag(Tags\HTTP_METHOD, $request->getMethod());
            $span->tag(Tags\HTTP_PATH, $request->getUri()->getPath());
            $scopeCloser = $tracing->getTracer()->openScope($span);

            $injector = $tracing->getPropagation()->getInjector(new RequestHeaders());
            $injector($span->getContext(), $request);

            $span->start();
            return $handler($request, $options)->then(
                function (ResponseInterface $response) use ($span, $scopeCloser) {
                    $span->tag(Tags\HTTP_STATUS_CODE, $response->getStatusCode());
                    if ($response->getStatusCode() > 399) {
                        $span->tag(Tags\ERROR, true);
                    }
                    $span->finish();
                    $scopeCloser();
                    return $response;
                },
                function ($reason) use ($span, $scopeCloser) {
                    $response = $reason instanceof RequestException
                        ? $reason->getResponse()
                        : null;
                    $span->tag(Tags\ERROR, true);
                    if ($response !== null) {
                        $span->tag(Tags\HTTP_STATUS_CODE, $response->getStatusCode());
                    }
                    $span->finish();
                    $scopeCloser();
                    return Promise\rejection_for($reason);
                }
            );
        };
    };
}
