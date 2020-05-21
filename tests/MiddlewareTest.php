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
use Zipkin\Reporters\InMemory as InMemoryReporter;

final class MiddlewareTest extends PHPUnit_Framework_TestCase
{
    const METHOD = 'POST';
    const METHOD_LOWERCASE = 'post';
    const URI = 'http://domain.com/test?key=value';
    const HEADER_KEY = 'test_key';
    const HEADER_VALUE = 'test_value';
    const TAG_KEY = 'tag_key';
    const TAG_VALUE = 'tag_value';
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
        $middleware = Middleware\tracing($tracing, [self::TAG_KEY => self::TAG_VALUE]);
        $handler = HandlerStack::create(new MockHandler([$expectedResponse]));
        $handler->push($middleware);

        $client = new Client([
            'handler' => $handler,
        ]);

        if ($expectedResponse instanceof RequestException
            || ($expectedResponse instanceof Response && $expectedResponse->getStatusCode() > 399)) {
            $this->expectException(RequestException::class);
        }

        $actualResponse = $client->send($request);
        $this->assertSame($expectedResponse, $actualResponse);

        $tracing->getTracer()->flush();

        $spans = $reporter->flush();

        $this->assertCount(1, $spans);

        $arraySpan = $spans[0]->toArray();
        $this->assertNotNull($arraySpan['timestamp']);
        $this->assertNotNull($arraySpan['duration']);

        foreach ($expectedSpanSubset as $key => $value) {
            $this->assertEquals($value, $arraySpan[$key]);
        }
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
                    'name' => 'http/' . self::METHOD_LOWERCASE,
                    'debug' => false,
                    'localEndpoint' => [
                        'serviceName' => 'cli',
                    ],
                    'kind' => 'CLIENT',
                    'tags' => [
                        'http.method' => self::METHOD,
                        'http.path' => '/test',
                        self::TAG_KEY => self::TAG_VALUE,
                        'http.status_code' => '200',
                    ],
                ],
                new Response(200)
            ],
            [
                [
                    'name' => 'http/' . self::METHOD_LOWERCASE,
                    'debug' => false,
                    'localEndpoint' => [
                        'serviceName' => 'cli',
                    ],
                    'kind' => 'CLIENT',
                    'tags' => [
                        'http.method' => self::METHOD,
                        'http.path' => '/test',
                        self::TAG_KEY => self::TAG_VALUE,
                        'http.status_code' => '300',
                    ],
                ],
                new Response(300)
            ],
            [
                [
                    'name' => 'http/' . self::METHOD_LOWERCASE,
                    'debug' => false,
                    'localEndpoint' => [
                        'serviceName' => 'cli',
                    ],
                    'kind' => 'CLIENT',
                    'tags' => [
                        'http.method' => self::METHOD,
                        'http.path' => '/test',
                        self::TAG_KEY => self::TAG_VALUE,
                        'http.status_code' => '300',
                        'error' => 'true',
                    ],
                ],
                new Response(400)
            ],
            [
                [
                    'name' => 'http/' . self::METHOD_LOWERCASE,
                    'debug' => false,
                    'localEndpoint' => [
                        'serviceName' => 'cli',
                    ],
                    'kind' => 'CLIENT',
                    'tags' => [
                        'http.method' => self::METHOD,
                        'http.path' => '/test',
                        self::TAG_KEY => self::TAG_VALUE,
                        'http.status_code' => '400',
                        'error' => 'true',
                    ],
                ],
                new RequestException('Error 400', $this->getRequest(), new Response(400))
            ],
            [
                [
                    'name' => 'http/' . self::METHOD_LOWERCASE,
                    'debug' => false,
                    'localEndpoint' => [
                        'serviceName' => 'cli',
                    ],
                    'kind' => 'CLIENT',
                    'tags' => [
                        'http.method' => self::METHOD,
                        'http.path' => '/test',
                        'http.status_code' => '500',
                        self::TAG_KEY => self::TAG_VALUE,
                        'error' => 'true',
                    ],
                ],
                new RequestException('Error 500', $this->getRequest(), new Response(500))
            ],
        ];
    }
}
