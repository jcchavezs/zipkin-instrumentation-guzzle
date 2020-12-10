# Zipkin instrumentation for Guzzle 

![CI](https://github.com/jcchavezs/zipkin-instrumentation-guzzle/workflows/CI/badge.svg)
[![Latest Stable Version](https://poser.pugx.org/jcchavezs/zipkin-instrumentation-guzzle/v/stable)](https://packagist.org/packages/jcchavezs/zipkin-instrumentation-guzzle)
[![Total Downloads](https://poser.pugx.org/jcchavezs/zipkin-instrumentation-guzzle/downloads)](https://packagist.org/packages/jcchavezs/zipkin-instrumentation-guzzle)
[![License](https://poser.pugx.org/jcchavezs/zipkin-instrumentation-guzzle/license)](https://packagist.org/packages/jcchavezs/zipkin-instrumentation-guzzle)

Zipkin instrumentation for Guzzle HTTP Client.

## Install

```bash
composer require jcchavezs/zipkin-instrumentation-guzzle
```

## Usage

`ZipkinGuzzle\Middleware` is an [Guzzle middleware](http://docs.guzzlephp.org/en/stable/handlers-and-middleware.html) that can be used along with `GuzzleHttp\Client` to create a span and propagate the context.

### Default handler

You can use the default handler to easy the instrumentation:

```php
use Zipkin\TracingBuilder;
use ZipkinGuzzle\Middleware;

$tracing = TracingBuilder::create()->build();

// Default tags for all spans being created. They are not mandatory.
$tags = [
   'instance' => $_SERVER['SERVER_NAME']
];

$client = new Client([
    'handler' => Middleware\handlerStack($tracing, $tags),
]);
```

### Customizing handler

If you need to customize the tracing handler (e.g. wrapping it with another handler) you can create a `GuzzleHttp\HandlerStack` and push/unshift handlers into it making sure the **tracing middleware stays at the top of the stack**:

```php
use GuzzleHttp\HandlerStack;
use Zipkin\TracingBuilder;
use ZipkinGuzzle\Middleware;

$tracing = TracingBuilder::create()->build();

$stack = HandlerStack::create();
$stack->push(someMiddleware());
...
$stack->push(Middleware\tracing($tracing));

$client = new Client([
    'handler' => $stack,
]);
```

## Guzzle 7

Guzzle 7 is [compatible with PSR18 clients](https://github.com/guzzle/guzzle/blob/7.2.0/CHANGELOG.md#700-beta1---2019-12-30), hence you can use the native Zipkin instrumentation.
Check https://github.com/openzipkin/zipkin-php/tree/master/src/Zipkin/Instrumentation/Http/Client/Psr18#usage for more details.
