<?php

namespace ZipkinGuzzle\Tests\Http;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use PHPUnit_Framework_TestCase;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zipkin\TracingBuilder;
use ZipkinGuzzle\Http\Client;

final class ClientTest extends PHPUnit_Framework_TestCase
{
    const METHOD = 'POST';
    const URI = 'http://domain.com/test?key=value';
    const HEADER_KEY = 'test_key';
    const HEADER_VALUE = 'test_value';
    const BODY = 'test_body';

    public function testSend()
    {
        $request = new Request(self::METHOD, self::URI, [
            self::HEADER_KEY => self::HEADER_VALUE,
        ], self::BODY);
        $options = [];

        $response = new Response();

        $tracing = TracingBuilder::create()->build();
        $client = $this->prophesize(ClientInterface::class);
        $client->send(Argument::that(function(RequestInterface $request) {
            return $request->hasHeader('X-B3-TraceId')
                && $request->hasHeader('X-B3-SpanId')
                && $request->hasHeader('X-B3-Sampled')
                && $request->hasHeader('X-B3-Flags');
        }), $options)->shouldBeCalled()->willReturn($response);
        $wrapperClient = new Client($tracing, $client->reveal());

        $response = $wrapperClient->send($request, $options);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertNull($tracing->getTracer()->getCurrentSpan()); // Scope is closed.
    }

    public function testSendAsync()
    {
        $request = new Request(self::METHOD, self::URI, [
            self::HEADER_KEY => self::HEADER_VALUE,
        ], self::BODY);
        $options = [];

        $promise = new Promise();

        $tracing = TracingBuilder::create()->build();
        $client = $this->prophesize(ClientInterface::class);
        $client->sendAsync(Argument::that(function(RequestInterface $request) {
            return $request->hasHeader('X-B3-TraceId')
                && $request->hasHeader('X-B3-SpanId')
                && $request->hasHeader('X-B3-Sampled')
                && $request->hasHeader('X-B3-Flags');
        }), $options)->shouldBeCalled()->willReturn($promise);
        $wrapperClient = new Client($tracing, $client->reveal());

        $promise = $wrapperClient->sendAsync($request, $options);

        $this->assertInstanceOf(Promise::class, $promise);
        $this->assertNull($tracing->getTracer()->getCurrentSpan()); // Scope is closed.
    }

    public function testRequest()
    {
        $response = new Response();

        $tracing = TracingBuilder::create()->build();
        $client = $this->prophesize(ClientInterface::class);
        $client->request(self::METHOD, new Uri(self::URI), Argument::that(function(array $options) {
            return $options['body'] === self::BODY
                && array_key_exists('x-b3-traceid', $options['headers'])
                && array_key_exists('x-b3-spanid', $options['headers'])
                && array_key_exists('x-b3-sampled', $options['headers'])
                && array_key_exists('x-b3-flags', $options['headers']);
        }))->shouldBeCalled()->willReturn($response);
        $wrapperClient = new Client($tracing, $client->reveal());

        $response = $wrapperClient->request(self::METHOD, self::URI, [
            'headers' => [self::HEADER_KEY => self::HEADER_VALUE],
            'body' => self::BODY,
        ]);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertNull($tracing->getTracer()->getCurrentSpan()); // Scope is closed.
    }

    public function testRequestAsync()
    {
        $promise = new Promise();

        $tracing = TracingBuilder::create()->build();
        $client = $this->prophesize(ClientInterface::class);
        $client->requestAsync(self::METHOD, new Uri(self::URI), Argument::that(function(array $options) {
            return $options['body'] === self::BODY
                && array_key_exists('x-b3-traceid', $options['headers'])
                && array_key_exists('x-b3-spanid', $options['headers'])
                && array_key_exists('x-b3-sampled', $options['headers'])
                && array_key_exists('x-b3-flags', $options['headers']);
        }))->shouldBeCalled()->willReturn($promise);
        $wrapperClient = new Client($tracing, $client->reveal());

        $promise = $wrapperClient->requestAsync(self::METHOD, self::URI, [
            'headers' => [self::HEADER_KEY => self::HEADER_VALUE],
            'body' => self::BODY,
        ]);

        $this->assertInstanceOf(Promise::class, $promise);
        $this->assertNull($tracing->getTracer()->getCurrentSpan()); // Scope is closed.
    }
}