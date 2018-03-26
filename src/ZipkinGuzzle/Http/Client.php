<?php

namespace ZipkinGuzzle\Http;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zipkin\Kind;
use Zipkin\Propagation\Map;
use Zipkin\Tags;
use Zipkin\Tracing;

final class Client implements ClientInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var Tracing
     */
    private $tracing;

    public function __construct(Tracing $tracing, ClientInterface $client = null)
    {
        $this->tracing = $tracing;
        $this->client = $client ?: new GuzzleClient();
    }

    /**
     * {@inheritdoc}
     */
    public function send(RequestInterface $request, array $options = [])
    {
        $span = $this->tracing->getTracer()->nextSpan();
        $span->setName($request->getMethod());
        $span->setKind(Kind\CLIENT);
        $span->tag(Tags\HTTP_METHOD, $request->getMethod());
        $span->tag(Tags\HTTP_PATH, $request->getUri()->getPath());

        $scopeCloser = $this->tracing->getTracer()->openScope($span);

        try {
            $injector = $this->tracing->getPropagation()->getInjector(new RequestHeaders());
            $injector($span->getContext(), $request);
            $span->start();
            $response = $this->client->send($request, $options);
            $span->tag(Tags\HTTP_STATUS_CODE, $response->getStatusCode());
            return $response;
        } catch (GuzzleException $e) {
            $span->tag(Tags\ERROR, $e->getMessage());
            throw $e;
        } finally {
            $span->finish();
            $scopeCloser();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function sendAsync(RequestInterface $request, array $options = [])
    {
        $span = $this->tracing->getTracer()->nextSpan();
        $span->setName($request->getMethod());
        $span->setKind(Kind\CLIENT);
        $span->tag(Tags\HTTP_METHOD, $request->getMethod());
        $span->tag(Tags\HTTP_PATH, $request->getUri()->getPath());

        $scopeCloser = $this->tracing->getTracer()->openScope($span);

        $injector = $this->tracing->getPropagation()->getInjector(new RequestHeaders());
        $injector($span->getContext(), $request);
        $promise = $this->client->sendAsync($request, $options)->then(
            function (ResponseInterface $response) use ($span) {
                $span->tag(Tags\HTTP_STATUS_CODE, $response->getStatusCode());
            },
            function (RequestException $e) use ($span) {
                $span->tag(Tags\ERROR, $e->getMessage());
            }
        );

        $span->finish();
        $scopeCloser();

        return $promise;
    }

    /**
     * {@inheritdoc}
     */
    public function request($method, $uri, array $options = [])
    {
        $uri = is_string($uri) ? new Uri($uri) : $uri;

        $span = $this->tracing->getTracer()->nextSpan();
        $span->setName($method);
        $span->setKind(Kind\CLIENT);
        $span->tag(Tags\HTTP_METHOD, $method);
        $span->tag(Tags\HTTP_PATH, $uri->getPath());

        $scopeCloser = $this->tracing->getTracer()->openScope($span);

        try {
            $headers = array_key_exists('headers', $options) ? $options['headers'] : [];
            $injector = $this->tracing->getPropagation()->getInjector(new Map());
            $injector($span->getContext(), $headers);
            $response = $this->client->request($method, $uri, ['headers' => $headers] + $options);
            $span->tag(Tags\HTTP_STATUS_CODE, $response->getStatusCode());
            return $response;
        } catch (GuzzleException $e) {
            $span->tag(Tags\ERROR, $e->getMessage());
            throw $e;
        } finally {
            $span->finish();
            $scopeCloser();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function requestAsync($method, $uri, array $options = [])
    {
        $uri = is_string($uri) ? new Uri($uri) : $uri;

        $span = $this->tracing->getTracer()->nextSpan();
        $span->setName($method);
        $span->setKind(Kind\CLIENT);
        $span->tag(Tags\HTTP_METHOD, $method);
        $span->tag(Tags\HTTP_PATH, $uri->getPath());

        $scopeCloser = $this->tracing->getTracer()->openScope($span);

        $headers = array_key_exists('headers', $options) ? $options['headers'] : [];
        $injector = $this->tracing->getPropagation()->getInjector(new Map());
        $injector($span->getContext(), $headers);
        $promise = $this->client->requestAsync($method, $uri, ['headers' => $headers] + $options)->then(
            function (ResponseInterface $response) use ($span) {
                $span->tag(Tags\HTTP_STATUS_CODE, $response->getStatusCode());
            },
            function (RequestException $e) use ($span) {
                $span->tag(Tags\ERROR, $e->getMessage());
            }
        );

        $span->finish();
        $scopeCloser();

        return $promise;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig($option = null)
    {
        return $this->client->getConfig($option);
    }
}
