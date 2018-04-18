# Zipkin instrumentation for Guzzle 

[![Build Status](https://travis-ci.org/jcchavezs/zipkin-instrumentation-guzzle.svg?branch=master)](https://travis-ci.org/jcchavezs/zipkin-instrumentation-guzzle)
[![Latest Stable Version](https://poser.pugx.org/jcchavezs/zipkin-instrumentation-guzzle/v/stable)](https://packagist.org/packages/jcchavezs/zipkin-instrumentation-guzzle)
[![Total Downloads](https://poser.pugx.org/jcchavezs/zipkin-instrumentation-guzzle/downloads)](https://packagist.org/packages/jcchavezs/zipkin-instrumentation-guzzle)
[![License](https://poser.pugx.org/jcchavezs/zipkin-instrumentation-guzzle/license)](https://packagist.org/packages/jcchavezs/zipkin-instrumentation-guzzle)

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

// Default tags for all spans being created.
$defaultTags = [
   'instance' => $_SERVER['SERVER_NAME']
];

$client = new Client([
    'handler' => Middleware\handlerStack(Tracing $tracing, $defaultTags),
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

$client = new Client([
    'handler' => $stack,
]);
```
