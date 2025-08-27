<?php

declare(strict_types=1);

namespace React\Promise\Tests\Unit\PromiseTest;

use PHPUnit\Framework\Attributes\Test;
use React\Promise\PromiseInterface;
use React\Promise\Tests\Unit\PromiseAdapter\PromiseAdapterInterface;

trait PromisePendingTestTrait
{
    abstract public function getPromiseTestAdapter(?callable $canceller = null): PromiseAdapterInterface;

    #[Test]
    public function thenShouldReturnAPromiseForPendingPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        self::assertInstanceOf(PromiseInterface::class, $adapter->promise()->then());
    }

    #[Test]
    public function thenShouldReturnAllowNullForPendingPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        self::assertInstanceOf(PromiseInterface::class, $adapter->promise()->then(null, null));
    }

    #[Test]
    public function catchShouldNotInvokeRejectionHandlerForPendingPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle(null);
        $adapter->promise()->catch($this->expectCallableNever());
    }

    #[Test]
    public function finallyShouldReturnAPromiseForPendingPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        self::assertInstanceOf(PromiseInterface::class, $adapter->promise()->finally(static function (): void {}));
    }

    /**
     * @deprecated
     */
    #[Test]
    public function otherwiseShouldNotInvokeRejectionHandlerForPendingPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle(null);
        $adapter->promise()->otherwise($this->expectCallableNever());
    }

    /**
     * @deprecated
     */
    #[Test]
    public function alwaysShouldReturnAPromiseForPendingPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        self::assertInstanceOf(PromiseInterface::class, $adapter->promise()->always(static function (): void {}));
    }
}
