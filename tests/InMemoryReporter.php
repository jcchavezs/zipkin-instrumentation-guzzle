<?php

namespace ZipkinGuzzle\Tests;

use Zipkin\Reporter;
use Zipkin\Recording\Span as MutableSpan;

final class InMemoryReporter implements Reporter
{
    private $spans = [];

    public function report(array $spans)
    {
        $this->spans = array_merge($this->spans, $spans);
    }

    /**
     * @return MutableSpan[]
     */
    public function getSpans()
    {
        return $this->spans;
    }
}
