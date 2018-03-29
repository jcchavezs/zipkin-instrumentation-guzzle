<?php

namespace ZipkinGuzzle\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware as GuzzleMiddleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit_Framework_TestCase;
use Zipkin\Samplers\BinarySampler;
use Zipkin\TracingBuilder;
use ZipkinGuzzle\Middleware;

final class MiddlewareTest extends PHPUnit_Framework_TestCase
{
    const METHOD = 'POST';
    const URI = 'http://domain.com/test?key=value';
    const HEADER_KEY = 'test_key';
    const HEADER_VALUE = 'test_value';
    const BODY = 'test_body';

    public function getRequest()
    {
        return new Request(self::METHOD, self::URI, [
            self::HEADER_KEY => self::HEADER_VALUE,
        ], self::BODY);
    }

    /**
     * @dataProvider middlewareDataProvider
     */
    public function testMiddlewareCreatesSpan($expectedSpanSubset, $expectedResponse)
    {
        $request = $this->getRequest();

        $reporter = new InMemoryReporter();
        $tracing = TracingBuilder::create()
            ->havingReporter($reporter)
            ->havingSampler(BinarySampler::createAsAlwaysSample())
            ->build();
        $middleware = Middleware\tracing($tracing);
        $handler = HandlerStack::create(new MockHandler([$expectedResponse]));
        $handler->push($middleware);

        $client = new Client([
            'handler' => $handler,
        ]);

        if ($expectedResponse instanceof RequestException) {
            $this->expectException(RequestException::Class);
        }

        $actualResponse = $client->send($request);
        $this->assertSame($expectedResponse, $actualResponse);

        $tracing->getTracer()->flush();

        $this->assertCount(1, $reporter->getSpans());

        $arraySpan = $reporter->getSpans()[0]->toArray();
        $this->assertNotNull($arraySpan['timestamp']);
        $this->assertNotNull($arraySpan['duration']);
        $this->assertArraySubset($expectedSpanSubset, $arraySpan);
    }

    public function testMiddlewareInjectsHeaders()
    {
        $request = $this->getRequest();

        $tracing = TracingBuilder::create()
            ->havingSampler(BinarySampler::createAsAlwaysSample())
            ->build();
        $middleware = Middleware\tracing($tracing);
        $container = [];
        $history = GuzzleMiddleware::history($container);
        $handler = HandlerStack::create(new MockHandler([new Response(200)]));
        $handler->push($middleware);
        $handler->push($history);

        $client = new Client([
            'handler' => $handler,
        ]);

        $client->send($request);

        /**
         * @var Request $request
         */
        $request = $container[0]['request'];
        $this->assertNotNull($request->getHeader('X-B3-TraceId'));
        $this->assertNotNull($request->getHeader('X-B3-SpanId'));
        $this->assertNotNull($request->getHeader('X-B3-Sampled'));
        $this->assertNotNull($request->getHeader('X-B3-Flags'));
    }

    public function middlewareDataProvider()
    {
        return [
            [
                [
                    'name' => self::METHOD,
                    'debug' => false,
                    'localEndpoint' => [],
                    'kind' => 'CLIENT',
                    'tags' => [
                        'http.method' => self::METHOD,
                        'http.path' => '/test',
                        'http.status_code' => 200,
                    ],
                ],
                new Response(200)
            ],
            [
                [
                    'name' => self::METHOD,
                    'debug' => false,
                    'localEndpoint' => [],
                    'kind' => 'CLIENT',
                    'tags' => [
                        'http.method' => self::METHOD,
                        'http.path' => '/test',
                        'http.status_code' => 300,
                    ],
                ],
                new Response(300)
            ],
            [
                [
                    'name' => self::METHOD,
                    'debug' => false,
                    'localEndpoint' => [],
                    'kind' => 'CLIENT',
                    'tags' => [
                        'http.method' => self::METHOD,
                        'http.path' => '/test',
                        'http.status_code' => 400,
                        'error' => true,
                    ],
                ],
                new RequestException('Error 400', $this->getRequest(), new Response(400))
            ],
            [
                [
                    'name' => self::METHOD,
                    'debug' => false,
                    'localEndpoint' => [],
                    'kind' => 'CLIENT',
                    'tags' => [
                        'http.method' => self::METHOD,
                        'http.path' => '/test',
                        'http.status_code' => 500,
                        'error' => true,
                    ],
                ],
                new RequestException('Error 500', $this->getRequest(), new Response(500))
            ],
        ];
    }
}
