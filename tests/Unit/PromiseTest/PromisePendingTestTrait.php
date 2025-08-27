<?php

declare(strict_types=1);

namespace React\Promise\Unit\PromiseTest;

use React\Promise\PromiseInterface;
use React\Promise\Unit\PromiseAdapter\PromiseAdapterInterface;

trait PromisePendingTestTrait
{
    abstract public function getPromiseTestAdapter(?callable $canceller = null): PromiseAdapterInterface;

    /**
     * @test
     */
    public function thenShouldReturnAPromiseForPendingPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        self::assertInstanceOf(PromiseInterface::class, $adapter->promise()->then());
    }

    /**
     * @test
     */
    public function thenShouldReturnAllowNullForPendingPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        self::assertInstanceOf(PromiseInterface::class, $adapter->promise()->then(null, null));
    }

    /**
     * @test
     */
    public function catchShouldNotInvokeRejectionHandlerForPendingPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle(null);
        $adapter->promise()->catch($this->expectCallableNever());
    }

    /**
     * @test
     */
    public function finallyShouldReturnAPromiseForPendingPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        self::assertInstanceOf(PromiseInterface::class, $adapter->promise()->finally(static function (): void {}));
    }

    /**
     * @test
     * @deprecated
     */
    public function otherwiseShouldNotInvokeRejectionHandlerForPendingPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle(null);
        $adapter->promise()->otherwise($this->expectCallableNever());
    }

    /**
     * @test
     * @deprecated
     */
    public function alwaysShouldReturnAPromiseForPendingPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        self::assertInstanceOf(PromiseInterface::class, $adapter->promise()->always(static function (): void {}));
    }
}
