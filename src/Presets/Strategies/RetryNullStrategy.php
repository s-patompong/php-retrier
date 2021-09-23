<?php

namespace SPatompong\Retrier\Presets\Strategies;

use SPatompong\Retrier\Contracts\RetryStrategy;

class RetryNullStrategy implements RetryStrategy
{
    public function shouldRetry(mixed $value): bool
    {
        return is_null($value);
    }
}
