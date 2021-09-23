<?php

namespace SPatompong\Retrier\Presets\Strategies;

use SPatompong\Retrier\Contracts\RetryStrategy;
use Throwable;

class RetryThrowableStrategy implements RetryStrategy
{
    public function shouldRetry(mixed $value): bool
    {
        return $value instanceof Throwable;
    }
}
