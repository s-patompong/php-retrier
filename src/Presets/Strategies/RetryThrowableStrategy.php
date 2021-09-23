<?php

namespace SPatompong\Retrier\Presets\Strategies;

use SPatompong\Retrier\Contracts\RetryStrategy;
use Throwable;

class RetryThrowableStrategy implements RetryStrategy
{
    public function shouldRetry(mixed $value): bool
    {
        if ($value instanceof Throwable) {
            return true;
        }

        return false;
    }
}
