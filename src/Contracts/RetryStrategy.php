<?php

namespace SPatompong\Retrier\Contracts;

interface RetryStrategy
{
    /**
     * Add a logic to check if the retrier should retry
     *
     * @param mixed $value
     * @return bool
     */
    public function shouldRetry(mixed $value): bool;
}
