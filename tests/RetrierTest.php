<?php

namespace SPatompong\Retrier\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use SPatompong\Retrier\Exceptions\InvalidDelayException;
use SPatompong\Retrier\Exceptions\InvalidRetryTimesException;
use SPatompong\Retrier\Presets\Strategies\RetryNullStrategy;
use SPatompong\Retrier\Retrier;
use SPatompong\Retrier\Tests\helpers\FakeClass;

class RetrierTest extends TestCase
{
    /** @test */
    public function it_does_not_allow_less_than_one_retry_times(): void
    {
        $this->expectException(InvalidRetryTimesException::class);

        $retrier = new Retrier();
        $retrier->setRetryTimes(-1);
    }

    /** @test */
    public function it_does_not_allow_less_then_one_delay_time(): void
    {
        $this->expectException(InvalidDelayException::class);

        $retrier = new Retrier();
        $retrier->setDelay(-1);
    }

    /** @test */
    public function it_can_execute_the_logic(): void
    {
        $num = 0;

        $retrier = new Retrier();
        $newNum = $retrier->setLogic(function () use ($num) {
            return $num + 1;
        })->execute();

        $this->assertEquals(1, $newNum);
    }

    /** @test */
    public function it_can_retry_correct_number_of_retry_times(): void
    {
        $this->expectException(Exception::class);

        $retryCount = 0;

        $retrier = new Retrier();
        $retrier
            ->setRetryTimes(10)
            ->setDelay(0)
            ->setOnRetryListener(function () use (&$retryCount) {
                $retryCount++;
            })
            ->setLogic(fn() => throw new Exception('Test Exception'))
            ->execute();

        $this->assertEquals(10, $retryCount);
    }

    /** @test */
    public function it_can_retry_null_retry_strategy(): void
    {
        $retryCount = 0;

        $retrier = new Retrier();
        $shouldBeNull = $retrier
            ->setRetryStrategy(new RetryNullStrategy())
            ->setDelay(0)
            ->setOnRetryListener(function () use (&$retryCount) {
                $retryCount++;
            })
            ->setLogic(fn() => null)
            ->execute();

        $this->assertEquals(3, $retryCount);
        $this->assertNull($shouldBeNull);
    }

    /** @test */
    public function it_can_retry_public_method_of_a_class()
    {
        $fakeClass = new FakeClass();

        $result = (new Retrier())
            ->setLogic([$fakeClass, 'fakePublicMethod'])
            ->execute();

        $this->assertEquals($fakeClass->fakePublicMethod(), $result);
    }

    /** @test */
    public function it_can_retry_static_method_of_a_class()
    {
        $result = (new Retrier())
            ->setLogic([FakeClass::class, 'fakeStaticMethod'])
            ->execute();

        $this->assertEquals(FakeClass::fakeStaticMethod(), $result);
    }
}
