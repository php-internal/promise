<?php

declare(strict_types=1);

namespace React\Promise\PromiseTest;

trait PromisePendingTestTrait
{
    /**
     * @return \React\Promise\PromiseAdapter\PromiseAdapterInterface
     */
    abstract public function getPromiseTestAdapter(?callable $canceller = null);

    #[\PHPUnit\Framework\Attributes\Test]
    public function thenShouldReturnAPromiseForPendingPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->assertInstanceOf(\React\Promise\PromiseInterface::class, $adapter->promise()->then());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function thenShouldReturnAllowNullForPendingPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->assertInstanceOf(\React\Promise\PromiseInterface::class, $adapter->promise()->then(null, null, null));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function cancelShouldReturnNullForPendingPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->assertNull($adapter->promise()->cancel());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function doneShouldReturnNullForPendingPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->assertNull($adapter->promise()->done());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function doneShouldReturnAllowNullForPendingPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->assertNull($adapter->promise()->done(null, null, null));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function otherwiseShouldNotInvokeRejectionHandlerForPendingPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle();
        $adapter->promise()->otherwise($this->expectCallableNever());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function alwaysShouldReturnAPromiseForPendingPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->assertInstanceOf(\React\Promise\PromiseInterface::class, $adapter->promise()->always(static function (): void {}));
    }
}
