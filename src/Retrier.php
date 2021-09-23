<?php

namespace SPatompong\Retrier;

use Closure;
use SPatompong\Retrier\Contracts\RetryStrategy;
use SPatompong\Retrier\Exceptions\InvalidDelayException;
use SPatompong\Retrier\Exceptions\InvalidRetryTimesException;
use SPatompong\Retrier\Presets\Strategies\RetryThrowableStrategy;
use Throwable;

class Retrier
{
    /**
     * The retry strategy to use
     *
     * @var RetryStrategy
     */
    private RetryStrategy $retryStrategy;

    /**
     * A logic to execute, it MUST return something
     *
     * @var Closure
     */
    private Closure $logic;

    /**
     * A callable with a signature of:
     * ($currentRetryTimes, $value, $throwable): void
     *
     * @var Closure call
     */
    private Closure $onRetryListener;

    private int $retryTimes = 3;

    /**
     * The time to wait between each retry
     *
     * @var int
     */
    private int $delay = 3;

    public function __construct()
    {
        $this->retryStrategy = new RetryThrowableStrategy();

        $this->logic = function (): void {
        };
        $this->onRetryListener = function (): void {
        };
    }

    /**
     * @return RetryStrategy
     */
    public function getRetryStrategy(): RetryStrategy
    {
        return $this->retryStrategy;
    }

    /**
     * @param RetryStrategy $retryStrategy
     * @return Retrier
     */
    public function setRetryStrategy(RetryStrategy $retryStrategy): Retrier
    {
        $this->retryStrategy = $retryStrategy;
        return $this;
    }

    /**
     * @return Closure
     */
    public function getLogic(): Closure
    {
        return $this->logic;
    }

    /**
     * @param Closure $logic
     * @return Retrier
     */
    public function setLogic(Closure $logic): Retrier
    {
        $this->logic = $logic;
        return $this;
    }

    /**
     * @return int
     */
    public function getRetryTimes(): int
    {
        return $this->retryTimes;
    }

    /**
     * @param int $retryTimes
     * @return Retrier
     * @throws InvalidRetryTimesException
     */
    public function setRetryTimes(int $retryTimes): Retrier
    {
        if ($retryTimes < 0) {
            throw new InvalidRetryTimesException("Retry time must be >= 0.");
        }

        $this->retryTimes = $retryTimes;
        return $this;
    }

    /**
     * @return int
     */
    public function getDelay(): int
    {
        return $this->delay;
    }

    /**
     * @param int $delay
     * @return Retrier
     * @throws InvalidDelayException
     */
    public function setDelay(int $delay): Retrier
    {
        if ($delay < 0) {
            throw new InvalidDelayException("Delay must be >= 0.");
        }

        $this->delay = $delay;
        return $this;
    }

    /**
     * @return Closure
     */
    public function getOnRetryListener(): Closure
    {
        return $this->onRetryListener;
    }

    /**
     * @param Closure $onRetryListener
     * @return Retrier
     */
    public function setOnRetryListener(Closure $onRetryListener): Retrier
    {
        $this->onRetryListener = $onRetryListener;
        return $this;
    }

    /**
     * @throws Throwable
     */
    public function execute(): mixed
    {
        // Initialize some variables
        $try = 0;
        $value = null;
        $throwable = null;

        // We will keep looping until the number of retryTimes
        while ($try < $this->retryTimes) {
            // Reset each variable value in each loop
            $try++;
            $throwable = null;
            $value = null;

            try {
                // Call the logic function
                $value = call_user_func($this->logic);

                // Return the value early if we don't need to retry it
                if (!$this->retryStrategy->shouldRetry($value)) {
                    return $value;
                }
            } catch (Throwable $t) {
                // Throw the exception early if we don't need to retry it
                if (!$this->retryStrategy->shouldRetry($t)) {
                    throw $t;
                }

                // Otherwise, keep the throwable
                $throwable = $t;
            }

            // The code failed to get the valid value again, we will run the listener closure
            call_user_func($this->onRetryListener, $try, $value, $throwable);

            // Only wait if it's not the last retry
            if ($try < $this->retryTimes) {
                sleep($this->delay);
            }
        }

        // If after finish the user func and the throwable is not null
        // We need to throw it
        if (!is_null($throwable)) {
            throw $throwable;
        }

        // Otherwise, we can return the value
        return $value;
    }
}
