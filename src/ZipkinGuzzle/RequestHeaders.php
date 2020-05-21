<?php

namespace ZipkinGuzzle;

use Psr\Http\Message\RequestInterface;
use Zipkin\Propagation\Exceptions\InvalidPropagationCarrier;
use Zipkin\Propagation\Exceptions\InvalidPropagationKey;
use Zipkin\Propagation\Setter;

final class RequestHeaders implements Setter
{
    /**
     * @param RequestInterface $carrier
     * {@inheritdoc}
     */
    public function put(&$carrier, $key, $value)
    {
        if ($key !== (string) $key) {
            throw InvalidPropagationKey::forInvalidKey($key);
        }

        if ($key === '') {
            throw InvalidPropagationKey::forEmptyKey();
        }
        
        if ($carrier instanceof RequestInterface) {
            $carrier = $carrier->withHeader($key, $value);
            return;
        }

        throw InvalidPropagationCarrier::forCarrier($carrier);
    }
}
