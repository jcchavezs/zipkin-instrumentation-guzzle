<?php

namespace ZipkinGuzzle\Tests\Http;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use PHPUnit_Framework_TestCase;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zipkin\Samplers\BinarySampler;
use Zipkin\Tags;
use Zipkin\Tracing;
use Zipkin\TracingBuilder;
use ZipkinGuzzle\Http\Client;
use GuzzleHttp\Exception\ServerException;

final class ClientTest extends PHPUnit_Framework_TestCase
{
    const METHOD = 'POST';
    const URI = 'http://domain.com/test?key=value';
    const HEADER_KEY = 'test_key';
    const HEADER_VALUE = 'test_value';
    const BODY = 'test_body';

    /**
     * @var InMemoryReporter
     */
    private $reporter;

    /**
     * @var Tracing
     */
    private $tracing;

    protected function setUp()
    {
        $this->reporter = new InMemoryReporter();
        $this->tracing = TracingBuilder::create()
            ->havingSampler(BinarySampler::createAsAlwaysSample())
            ->havingReporter($this->reporter)
            ->build();

        parent::setUp();
    }

    public function testSendSuccess()
    {
        $request = new Request(self::METHOD, self::URI, [
            self::HEADER_KEY => self::HEADER_VALUE,
        ], self::BODY);
        $options = [];

        $response = new Response();

        $client = $this->prophesize(ClientInterface::class);
        $client->send(Argument::that(function (RequestInterface $request) {
            return $request->hasHeader('X-B3-TraceId')
                && $request->hasHeader('X-B3-SpanId')
                && $request->hasHeader('X-B3-Sampled')
                && $request->hasHeader('X-B3-Flags');
        }), $options)->shouldBeCalled()->willReturn($response);
        $wrapperClient = new Client($this->tracing, $client->reveal());

        $response = $wrapperClient->send($request, $options);

        $this->tracing->getTracer()->flush();

        $createdSpan = $this->reporter->getSpans()[0]->toArray();
        $this->assertCount(1, $this->reporter->getSpans());
        $this->assertEquals(self::METHOD, $createdSpan['name']);
        $this->assertEquals('CLIENT', $createdSpan['kind']);
        $this->assertEquals([
            Tags\HTTP_METHOD => self::METHOD,
            Tags\HTTP_PATH => '/test',
            Tags\HTTP_STATUS_CODE => 200,
        ], $createdSpan['tags']);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertNull($this->tracing->getTracer()->getCurrentSpan()); // Scope is closed.
    }

    public function testSendFails()
    {
        $request = new Request(self::METHOD, self::URI, [
            self::HEADER_KEY => self::HEADER_VALUE,
        ], self::BODY);
        $options = [];

        $this->expectException(GuzzleException::class);

        $client = $this->prophesize(ClientInterface::class);
        $client->send(Argument::that(function (RequestInterface $request) {
            return $request->hasHeader('X-B3-TraceId')
                && $request->hasHeader('X-B3-SpanId')
                && $request->hasHeader('X-B3-Sampled')
                && $request->hasHeader('X-B3-Flags');
        }), $options)->shouldBeCalled()->willThrow(new ServerException('', $request));
        $wrapperClient = new Client($this->tracing, $client->reveal());

        $response = $wrapperClient->send($request, $options);

        $this->tracing->getTracer()->flush();

        $createdSpan = $this->reporter->getSpans()[0]->toArray();
        $this->assertCount(1, $this->reporter->getSpans());
        $this->assertEquals(self::METHOD, $createdSpan['name']);
        $this->assertEquals('CLIENT', $createdSpan['kind']);
        $this->assertEquals([
            Tags\HTTP_METHOD => self::METHOD,
            Tags\HTTP_PATH => '/test',
            Tags\HTTP_STATUS_CODE => 500,
        ], $createdSpan['tags']);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertNull($this->tracing->getTracer()->getCurrentSpan()); // Scope is closed.
    }

    public function testSendAsyncSuccess()
    {
        $request = new Request(self::METHOD, self::URI, [
            self::HEADER_KEY => self::HEADER_VALUE,
        ], self::BODY);
        $options = [];

        $promise = new Promise();

        $client = $this->prophesize(ClientInterface::class);
        $client->sendAsync(Argument::that(function (RequestInterface $request) {
            return $request->hasHeader('X-B3-TraceId')
                && $request->hasHeader('X-B3-SpanId')
                && $request->hasHeader('X-B3-Sampled')
                && $request->hasHeader('X-B3-Flags');
        }), $options)->shouldBeCalled()->willReturn($promise);
        $wrapperClient = new Client($this->tracing, $client->reveal());

        $promise = $wrapperClient->sendAsync($request, $options);

        $this->tracing->getTracer()->flush();

        $createdSpan = $this->reporter->getSpans()[0]->toArray();
        $this->assertCount(1, $this->reporter->getSpans());
        $this->assertEquals(self::METHOD, $createdSpan['name']);
        $this->assertEquals('CLIENT', $createdSpan['kind']);
        $this->assertEquals([
            Tags\HTTP_METHOD => self::METHOD,
            Tags\HTTP_PATH => '/test',
        ], $createdSpan['tags']);

        $this->assertInstanceOf(Promise::class, $promise);
        $this->assertNull($this->tracing->getTracer()->getCurrentSpan()); // Scope is closed.
    }

    public function testSendAsyncFails()
    {
        $request = new Request(self::METHOD, self::URI, [
            self::HEADER_KEY => self::HEADER_VALUE,
        ], self::BODY);
        $options = [];

        $promise = new Promise();

        $client = $this->prophesize(ClientInterface::class);
        $client->sendAsync(Argument::that(function (RequestInterface $request) {
            return $request->hasHeader('X-B3-TraceId')
                && $request->hasHeader('X-B3-SpanId')
                && $request->hasHeader('X-B3-Sampled')
                && $request->hasHeader('X-B3-Flags');
        }), $options)->shouldBeCalled()->willReturn($promise);
        $wrapperClient = new Client($this->tracing, $client->reveal());

        $promise = $wrapperClient->sendAsync($request, $options);

        $this->tracing->getTracer()->flush();

        $createdSpan = $this->reporter->getSpans()[0]->toArray();
        $this->assertCount(1, $this->reporter->getSpans());
        $this->assertEquals(self::METHOD, $createdSpan['name']);
        $this->assertEquals('CLIENT', $createdSpan['kind']);
        $this->assertEquals([
            Tags\HTTP_METHOD => self::METHOD,
            Tags\HTTP_PATH => '/test',
        ], $createdSpan['tags']);

        $this->assertInstanceOf(Promise::class, $promise);
        $this->assertNull($this->tracing->getTracer()->getCurrentSpan()); // Scope is closed.
    }

    public function testRequestSuccess()
    {
        $response = new Response();

        $client = $this->prophesize(ClientInterface::class);
        $client->request(self::METHOD, new Uri(self::URI), Argument::that(function (array $options) {
            return $options['body'] === self::BODY
                && array_key_exists('x-b3-traceid', $options['headers'])
                && array_key_exists('x-b3-spanid', $options['headers'])
                && array_key_exists('x-b3-sampled', $options['headers'])
                && array_key_exists('x-b3-flags', $options['headers']);
        }))->shouldBeCalled()->willReturn($response);
        $wrapperClient = new Client($this->tracing, $client->reveal());

        $response = $wrapperClient->request(self::METHOD, self::URI, [
            'headers' => [self::HEADER_KEY => self::HEADER_VALUE],
            'body' => self::BODY,
        ]);

        $this->tracing->getTracer()->flush();

        $createdSpan = $this->reporter->getSpans()[0]->toArray();
        $this->assertCount(1, $this->reporter->getSpans());
        $this->assertEquals(self::METHOD, $createdSpan['name']);
        $this->assertEquals('CLIENT', $createdSpan['kind']);
        $this->assertEquals([
            Tags\HTTP_METHOD => self::METHOD,
            Tags\HTTP_PATH => '/test',
            Tags\HTTP_STATUS_CODE => 200,
        ], $createdSpan['tags']);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertNull($this->tracing->getTracer()->getCurrentSpan()); // Scope is closed.
    }

    public function testRequestFails()
    {
        $this->expectException(GuzzleException::class);

        $client = $this->prophesize(ClientInterface::class);
        $client->request(self::METHOD, new Uri(self::URI), Argument::that(function (array $options) {
            return $options['body'] === self::BODY
                && array_key_exists('x-b3-traceid', $options['headers'])
                && array_key_exists('x-b3-spanid', $options['headers'])
                && array_key_exists('x-b3-sampled', $options['headers'])
                && array_key_exists('x-b3-flags', $options['headers']);
        }))->shouldBeCalled()->willThrow(new ServerException('', new Request(self::METHOD, "/")));
        $wrapperClient = new Client($this->tracing, $client->reveal());

        $response = $wrapperClient->request(self::METHOD, self::URI, [
            'headers' => [self::HEADER_KEY => self::HEADER_VALUE],
            'body' => self::BODY,
        ]);

        $this->tracing->getTracer()->flush();

        $createdSpan = $this->reporter->getSpans()[0]->toArray();
        $this->assertCount(1, $this->reporter->getSpans());
        $this->assertEquals(self::METHOD, $createdSpan['name']);
        $this->assertEquals('CLIENT', $createdSpan['kind']);
        $this->assertEquals([
            Tags\HTTP_METHOD => self::METHOD,
            Tags\HTTP_PATH => '/test',
            Tags\HTTP_STATUS_CODE => 200,
        ], $createdSpan['tags']);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertNull($this->tracing->getTracer()->getCurrentSpan()); // Scope is closed.
    }

    public function testRequestAsync()
    {
        $promise = new Promise();

        $client = $this->prophesize(ClientInterface::class);
        $client->requestAsync(self::METHOD, new Uri(self::URI), Argument::that(function (array $options) {
            return $options['body'] === self::BODY
                && array_key_exists('x-b3-traceid', $options['headers'])
                && array_key_exists('x-b3-spanid', $options['headers'])
                && array_key_exists('x-b3-sampled', $options['headers'])
                && array_key_exists('x-b3-flags', $options['headers']);
        }))->shouldBeCalled()->willReturn($promise);
        $wrapperClient = new Client($this->tracing, $client->reveal());

        $promise = $wrapperClient->requestAsync(self::METHOD, self::URI, [
            'headers' => [self::HEADER_KEY => self::HEADER_VALUE],
            'body' => self::BODY,
        ]);

        $this->tracing->getTracer()->flush();

        $createdSpan = $this->reporter->getSpans()[0]->toArray();
        $this->assertCount(1, $this->reporter->getSpans());
        $this->assertEquals(self::METHOD, $createdSpan['name']);
        $this->assertEquals('CLIENT', $createdSpan['kind']);
        $this->assertEquals([
            Tags\HTTP_METHOD => self::METHOD,
            Tags\HTTP_PATH => '/test',
        ], $createdSpan['tags']);

        $this->assertInstanceOf(Promise::class, $promise);
        $this->assertNull($this->tracing->getTracer()->getCurrentSpan()); // Scope is closed.
    }
}
