<?php

declare(strict_types=1);

namespace React\Promise\Tests\Unit\PromiseTest;

trait PromiseSettledTestTrait
{
    /**
     * @return \React\Promise\Tests\Unit\PromiseAdapter\PromiseAdapterInterface
     */
    abstract public function getPromiseTestAdapter(?callable $canceller = null);

    #[\PHPUnit\Framework\Attributes\Test]
    public function thenShouldReturnAPromiseForSettledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle();
        $this->assertInstanceOf(\React\Promise\PromiseInterface::class, $adapter->promise()->then());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function thenShouldReturnAllowNullForSettledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle();
        $this->assertInstanceOf(\React\Promise\PromiseInterface::class, $adapter->promise()->then(null, null, null));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function cancelShouldReturnNullForSettledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle();

        $this->assertNull($adapter->promise()->cancel());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function cancelShouldHaveNoEffectForSettledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter($this->expectCallableNever());

        $adapter->settle();

        $adapter->promise()->cancel();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function doneShouldReturnNullForSettledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle();
        $this->assertNull($adapter->promise()->done(null, static function (): void {}));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function doneShouldReturnAllowNullForSettledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle();
        $this->assertNull($adapter->promise()->done(null, static function (): void {}, null));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function progressShouldNotInvokeProgressHandlerForSettledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle();
        $adapter->promise()->progress($this->expectCallableNever());
        $adapter->notify();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function alwaysShouldReturnAPromiseForSettledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle();
        $this->assertInstanceOf(\React\Promise\PromiseInterface::class, $adapter->promise()->always(static function (): void {}));
    }
}
