# Zipkin instrumentation for Guzzle 

Zipkin instrumentation for Guzzle HTTP Client.

## Install

```bash
composer require jcchavezs/zipkin-instrumentation-guzzle
```

## Usage

`ZipkinGuzzle\Middleware` is an [Guzzle middleware](http://docs.guzzlephp.org/en/stable/handlers-and-middleware.html) that can be 
used along with `GuzzleHttp\Client` in order to create a span and propagate the context.

### Default handler

```php
use Zipkin\TracingBuilder;
use ZipkinGuzzle\Middleware;

$tracing = TracingBuilder::create()->build();

$client = new Client([
    'handler' => Middleware\defaultHandlerStack(Tracing $tracing),
]);
```

### Custom handler

```php
use GuzzleHttp\HandlerStack;
use Zipkin\TracingBuilder;
use ZipkinGuzzle\Middleware;

$tracing = TracingBuilder::create()->build();

$stack = HandlerStack::create();
    $stack->push(Middleware\tracing($tracing));
    return $stack;

$client = new Client([
    'handler' => $stack,
]);
```