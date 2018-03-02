<?php

namespace ZipkinGuzzle;

use Guzzle\Common\Collection;
use Guzzle\Common\Event;
use Guzzle\Common\Exception\InvalidArgumentException;
use Guzzle\Http\ClientInterface;
use Guzzle\Http\EntityBodyInterface;
use Guzzle\Http\Message\EntityEnclosingRequestInterface;
use Guzzle\Http\Message\RequestInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class Client implements ClientInterface
{
    private $client;

    public function __construct($baseUrl = '', $config = null)
    {
        $this->client = new \Guzzle\Http\Client($baseUrl, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function setConfig($config)
    {
        $this->client->setConfig($config);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig($key = false)
    {
        return $this->client->setConfig($key);
    }

    /**
     * {@inheritdoc}
     */
    public function createRequest(
        $method = RequestInterface::GET,
        $uri = null,
        $headers = null,
        $body = null,
        array $options = array()
    )
    {
        return $this->client->createRequest($method, $uri, $headers, $body, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function get($uri = null, $headers = null, $options = array())
    {
        $this->client->get($uri, $headers, $options);
    }

    /**
     * Create a HEAD request for the client
     *
     * @param string|array $uri Resource URI
     * @param array|Collection $headers HTTP headers
     * @param array $options Options to apply to the request
     *
     * @return RequestInterface
     * @see    Guzzle\Http\ClientInterface::createRequest()
     */
    public function head($uri = null, $headers = null, array $options = array())
    {
        // TODO: Implement head() method.
    }

    /**
     * Create a DELETE request for the client
     *
     * @param string|array $uri Resource URI
     * @param array|Collection $headers HTTP headers
     * @param string|resource|EntityBodyInterface $body Body to send in the request
     * @param array $options Options to apply to the request
     *
     * @return EntityEnclosingRequestInterface
     * @see    Guzzle\Http\ClientInterface::createRequest()
     */
    public function delete($uri = null, $headers = null, $body = null, array $options = array())
    {
        // TODO: Implement delete() method.
    }

    /**
     * Create a PUT request for the client
     *
     * @param string|array $uri Resource URI
     * @param array|Collection $headers HTTP headers
     * @param string|resource|EntityBodyInterface $body Body to send in the request
     * @param array $options Options to apply to the request
     *
     * @return EntityEnclosingRequestInterface
     * @see    Guzzle\Http\ClientInterface::createRequest()
     */
    public function put($uri = null, $headers = null, $body = null, array $options = array())
    {
        // TODO: Implement put() method.
    }

    /**
     * Create a PATCH request for the client
     *
     * @param string|array $uri Resource URI
     * @param array|Collection $headers HTTP headers
     * @param string|resource|EntityBodyInterface $body Body to send in the request
     * @param array $options Options to apply to the request
     *
     * @return EntityEnclosingRequestInterface
     * @see    Guzzle\Http\ClientInterface::createRequest()
     */
    public function patch($uri = null, $headers = null, $body = null, array $options = array())
    {
        // TODO: Implement patch() method.
    }

    /**
     * Create a POST request for the client
     *
     * @param string|array $uri Resource URI
     * @param array|Collection $headers HTTP headers
     * @param array|Collection|string|EntityBodyInterface $postBody POST body. Can be a string, EntityBody, or
     *                                                    associative array of POST fields to send in the body of the
     *                                                    request. Prefix a value in the array with the @ symbol to
     *                                                    reference a file.
     * @param array $options Options to apply to the request
     *
     * @return EntityEnclosingRequestInterface
     * @see    Guzzle\Http\ClientInterface::createRequest()
     */
    public function post($uri = null, $headers = null, $postBody = null, array $options = array())
    {
        // TODO: Implement post() method.
    }

    /**
     * Create an OPTIONS request for the client
     *
     * @param string|array $uri Resource URI
     * @param array $options Options to apply to the request
     *
     * @return RequestInterface
     * @see    Guzzle\Http\ClientInterface::createRequest()
     */
    public function options($uri = null, array $options = array())
    {
        // TODO: Implement options() method.
    }

    /**
     * Sends a single request or an array of requests in parallel
     *
     * @param array|RequestInterface $requests One or more RequestInterface objects to send
     *
     * @return \Guzzle\Http\Message\Response|array Returns a single Response or an array of Response objects
     */
    public function send($requests)
    {
        // TODO: Implement send() method.
    }

    /**
     * Get the client's base URL as either an expanded or raw URI template
     *
     * @param bool $expand Set to FALSE to get the raw base URL without URI template expansion
     *
     * @return string|null
     */
    public function getBaseUrl($expand = true)
    {
        // TODO: Implement getBaseUrl() method.
    }

    /**
     * Set the base URL of the client
     *
     * @param string $url The base service endpoint URL of the webservice
     *
     * @return self
     */
    public function setBaseUrl($url)
    {
        // TODO: Implement setBaseUrl() method.
    }

    /**
     * Set the User-Agent header to be used on all requests from the client
     *
     * @param string $userAgent User agent string
     * @param bool $includeDefault Set to true to prepend the value to Guzzle's default user agent string
     *
     * @return self
     */
    public function setUserAgent($userAgent, $includeDefault = false)
    {
        // TODO: Implement setUserAgent() method.
    }

    /**
     * Set SSL verification options.
     *
     * Setting $certificateAuthority to TRUE will result in the bundled cacert.pem being used to verify against the
     * remote host.
     *
     * Alternate certificates to verify against can be specified with the $certificateAuthority option set to the full
     * path to a certificate file, or the path to a directory containing certificates.
     *
     * Setting $certificateAuthority to FALSE will turn off peer verification, unset the bundled cacert.pem, and
     * disable host verification. Please don't do this unless you really know what you're doing, and why you're doing
     * it.
     *
     * @param string|bool $certificateAuthority bool, file path, or directory path
     * @param bool $verifyPeer FALSE to stop from verifying the peer's certificate.
     * @param int $verifyHost Set to 1 to check the existence of a common name in the SSL peer
     *                                          certificate. 2 to check the existence of a common name and also verify
     *                                          that it matches the hostname provided.
     * @return self
     */
    public function setSslVerification($certificateAuthority = true, $verifyPeer = true, $verifyHost = 2)
    {
        // TODO: Implement setSslVerification() method.
    }

    /**
     * Get a list of all of the events emitted from the class
     *
     * @return array
     */
    public static function getAllEvents()
    {
        // TODO: Implement getAllEvents() method.
    }

    /**
     * Set the EventDispatcher of the request
     *
     * @param EventDispatcherInterface $eventDispatcher
     *
     * @return self
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        // TODO: Implement setEventDispatcher() method.
    }

    /**
     * Get the EventDispatcher of the request
     *
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        // TODO: Implement getEventDispatcher() method.
    }

    /**
     * Helper to dispatch Guzzle events and set the event name on the event
     *
     * @param string $eventName Name of the event to dispatch
     * @param array $context Context of the event
     *
     * @return Event Returns the created event object
     */
    public function dispatch($eventName, array $context = array())
    {
        // TODO: Implement dispatch() method.
    }

    /**
     * Add an event subscriber to the dispatcher
     *
     * @param EventSubscriberInterface $subscriber Event subscriber
     *
     * @return self
     */
    public function addSubscriber(EventSubscriberInterface $subscriber)
    {
        // TODO: Implement addSubscriber() method.
    }
}
