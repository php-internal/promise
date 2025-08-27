<?php

declare(strict_types=1);

namespace React\Promise\Tests\Unit\PromiseTest;

use PHPUnit\Framework\Attributes\Test;
use React\Promise\Internal\RejectedPromise;
use React\Promise\PromiseInterface;
use React\Promise\Tests\Unit\PromiseAdapter\PromiseAdapterInterface;

trait PromiseSettledTestTrait
{
    abstract public function getPromiseTestAdapter(?callable $canceller = null): PromiseAdapterInterface;

    #[Test]
    public function thenShouldReturnAPromiseForSettledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle(null);
        self::assertInstanceOf(PromiseInterface::class, $adapter->promise()->then());

        if ($adapter->promise() instanceof RejectedPromise) {
            $adapter->promise()->then(null, $this->expectCallableOnce()); // avoid reporting unhandled rejection
        }
    }

    #[Test]
    public function thenShouldReturnAllowNullForSettledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle(null);
        self::assertInstanceOf(PromiseInterface::class, $adapter->promise()->then(null, null));

        if ($adapter->promise() instanceof RejectedPromise) {
            $adapter->promise()->then(null, $this->expectCallableOnce()); // avoid reporting unhandled rejection
        }
    }

    #[Test]
    public function cancelShouldHaveNoEffectForSettledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter($this->expectCallableNever());

        $adapter->settle(null);

        $adapter->promise()->cancel();
    }

    #[Test]
    public function finallyShouldReturnAPromiseForSettledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle(null);
        self::assertInstanceOf(PromiseInterface::class, $promise = $adapter->promise()->finally(static function (): void {}));

        if ($promise instanceof RejectedPromise) {
            $promise->then(null, $this->expectCallableOnce()); // avoid reporting unhandled rejection
        }
    }

    /**
     * @deprecated
     */
    #[Test]
    public function alwaysShouldReturnAPromiseForSettledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle(null);
        self::assertInstanceOf(PromiseInterface::class, $promise = $adapter->promise()->always(static function (): void {}));

        if ($promise instanceof RejectedPromise) {
            $promise->then(null, $this->expectCallableOnce()); // avoid reporting unhandled rejection
        }
    }
}
