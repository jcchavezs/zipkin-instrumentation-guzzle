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
 * @param Tracing $tracing the tracing component
 * @param array $tags the default tags being added to the span.
 * @param array|callable[] $middlewares
 * @return HandlerStack
 */
function handlerStack(Tracing $tracing, array $tags = [], array $middlewares = [])
{
    $stack = HandlerStack::create();
    $stack->push(tracing($tracing, $tags));

    foreach ($middlewares as $middleware) {
        $stack->push($middleware);
    }

    return $stack;
}

/**
 * @param Tracing $tracing the tracing component
 * @param array $tags the default tags being added to the span.
 * @return callable
 */
function tracing(Tracing $tracing, array $tags = [])
{
    $tracer = $tracing->getTracer();
    $injector = $tracing->getPropagation()->getInjector(new RequestHeaders());
    
    return function (callable $handler) use ($tracer, $injector, $tags) {
        return function (RequestInterface $request, array $options) use ($handler, $tracer, $injector, $tags) {
            $span = $tracer->nextSpan();
            $span->setName($request->getMethod());
            $span->setKind(Kind\CLIENT);
            $span->tag(Tags\HTTP_METHOD, $request->getMethod());
            $span->tag(Tags\HTTP_PATH, $request->getUri()->getPath());

            foreach ($tags as $key => $value) {
                $span->tag($key, $value);
            }

            $scopeCloser = $tracer->openScope($span);
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
                    $error = true;
                    $response = null;
                    if ($reason instanceof RequestException) {
                        $response = $reason->getResponse();
                        $error = $reason->getMessage();
                    }
                    
                    $span->tag(Tags\ERROR, $error);
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
