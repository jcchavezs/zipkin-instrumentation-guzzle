# Zipkin instrumentation for Guzzle 

Zipkin instrumentation for Guzzle HTTP Client.

## Install

```bash
composer require jcchavezs/zipkin-instrumentation-guzzle
```

## Getting started

`ZipkinGuzzle\Http\Client` is an implementation for `GuzzleHttp\ClientInterface`
which decorates a real `GuzzleHttp\Client` 

### Default client

```php
use ZipkinGuzzle\Http\Client;

$client = new Client($tracer);
$client->request('GET', '/path');
```

### Injected client

```php
use GuzzleHttp\Client as GuzzleClient;

$decoratedClient = new GuzzleClient(['base_uri' => 'http://domain.com/']);
$client = new Client($tracer, $decoratedClient);
$client->request('GET', '/path');
```