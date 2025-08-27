<?php

declare(strict_types=1);

namespace React\Promise\Tests\Unit\PromiseTest;

use PHPUnit\Framework\Attributes\Test;
use React\Promise\PromiseInterface;
use React\Promise\Tests\Unit\PromiseAdapter\PromiseAdapterInterface;

use function React\Promise\reject;
use function React\Promise\resolve;

trait PromiseFulfilledTestTrait
{
    abstract public function getPromiseTestAdapter(?callable $canceller = null): PromiseAdapterInterface;

    #[Test]
    public function fulfilledPromiseShouldBeImmutable(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->resolve(1);
        $adapter->resolve(2);

        $adapter->promise()
            ->then(
                $mock,
                $this->expectCallableNever(),
            );
    }

    #[Test]
    public function fulfilledPromiseShouldInvokeNewlyAddedCallback(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->resolve(1);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then($mock, $this->expectCallableNever());
    }

    #[Test]
    public function thenShouldForwardResultWhenCallbackIsNull(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->resolve(1);
        $adapter->promise()
            ->then(
                null,
                $this->expectCallableNever(),
            )
            ->then(
                $mock,
                $this->expectCallableNever(),
            );
    }

    #[Test]
    public function thenShouldForwardCallbackResultToNextCallback(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $adapter->resolve(1);
        $adapter->promise()
            ->then(
                static fn($val) => $val + 1,
                $this->expectCallableNever(),
            )
            ->then(
                $mock,
                $this->expectCallableNever(),
            );
    }

    #[Test]
    public function thenShouldForwardPromisedCallbackResultValueToNextCallback(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $adapter->resolve(1);
        $adapter->promise()
            ->then(
                static fn($val) => resolve($val + 1),
                $this->expectCallableNever(),
            )
            ->then(
                $mock,
                $this->expectCallableNever(),
            );
    }

    #[Test]
    public function thenShouldSwitchFromCallbacksToErrbacksWhenCallbackReturnsARejection(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->resolve(1);
        $adapter->promise()
            ->then(
                static fn() => reject($exception),
                $this->expectCallableNever(),
            )
            ->then(
                $this->expectCallableNever(),
                $mock,
            );
    }

    #[Test]
    public function thenShouldSwitchFromCallbacksToErrbacksWhenCallbackThrows(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->throwException($exception));

        $mock2 = $this->createCallableMock();
        $mock2
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->resolve(1);
        $adapter->promise()
            ->then(
                $mock,
                $this->expectCallableNever(),
            )
            ->then(
                $this->expectCallableNever(),
                $mock2,
            );
    }

    /**
     * @requires PHP 8.1
     */
    #[Test]
    public function thenShouldContinueToExecuteCallbacksWhenPriorCallbackSuspendsFiber(): void
    {
        /** @var PromiseAdapterInterface<int> $adapter */
        $adapter = $this->getPromiseTestAdapter();
        $adapter->resolve(42);

        $fiber = new \Fiber(static function () use ($adapter): void {
            $adapter->promise()->then(static function (int $value): void {
                \Fiber::suspend($value);
            });
        });

        $ret = $fiber->start();
        $this->assertEquals(42, $ret);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(42));

        $adapter->promise()->then($mock);
    }

    #[Test]
    public function cancelShouldHaveNoEffectForFulfilledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter($this->expectCallableNever());

        $adapter->resolve(null);

        $adapter->promise()->cancel();
    }

    #[Test]
    public function catchShouldNotInvokeRejectionHandlerForFulfilledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->resolve(1);
        $adapter->promise()->catch($this->expectCallableNever());
    }

    #[Test]
    public function finallyShouldNotSuppressValueForFulfilledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $value = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($value));

        $adapter->resolve($value);
        $adapter->promise()
            ->finally(static function (): void {})
            ->then($mock);
    }

    #[Test]
    public function finallyShouldNotSuppressValueWhenHandlerReturnsANonPromiseForFulfilledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $value = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($value));

        $adapter->resolve($value);
        $adapter->promise()
            // @phpstan-ignore-line
            ->finally(static fn(): int => 1)
            ->then($mock);
    }

    #[Test]
    public function finallyShouldNotSuppressValueWhenHandlerReturnsAPromiseForFulfilledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $value = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($value));

        $adapter->resolve($value);
        $adapter->promise()
            // @phpstan-ignore-line
            ->finally(static fn(): PromiseInterface => resolve(1))
            ->then($mock);
    }

    #[Test]
    public function finallyShouldRejectWhenHandlerThrowsForFulfilledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->resolve(1);
        $adapter->promise()
            ->finally(static function () use ($exception): void {
                throw $exception;
            })
            ->then(null, $mock);
    }

    #[Test]
    public function finallyShouldRejectWhenHandlerRejectsForFulfilledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->resolve(1);
        $adapter->promise()
            ->finally(static fn() => reject($exception))
            ->then(null, $mock);
    }

    /**
     * @deprecated
     */
    #[Test]
    public function otherwiseShouldNotInvokeRejectionHandlerForFulfilledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->resolve(1);
        $adapter->promise()->otherwise($this->expectCallableNever());
    }

    /**
     * @deprecated
     */
    #[Test]
    public function alwaysShouldNotSuppressValueForFulfilledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $value = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($value));

        $adapter->resolve($value);
        $adapter->promise()
            ->always(static function (): void {})
            ->then($mock);
    }

    /**
     * @deprecated
     */
    #[Test]
    public function alwaysShouldNotSuppressValueWhenHandlerReturnsANonPromiseForFulfilledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $value = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($value));

        $adapter->resolve($value);
        $adapter->promise()
            // @phpstan-ignore-line
            ->always(static fn(): int => 1)
            ->then($mock);
    }

    /**
     * @deprecated
     */
    #[Test]
    public function alwaysShouldNotSuppressValueWhenHandlerReturnsAPromiseForFulfilledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $value = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($value));

        $adapter->resolve($value);
        $adapter->promise()
            // @phpstan-ignore-line
            ->always(static fn(): PromiseInterface => resolve(1))
            ->then($mock);
    }

    /**
     * @deprecated
     */
    #[Test]
    public function alwaysShouldRejectWhenHandlerThrowsForFulfilledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->resolve(1);
        $adapter->promise()
            ->always(static function () use ($exception): void {
                throw $exception;
            })
            ->then(null, $mock);
    }

    /**
     * @deprecated
     */
    #[Test]
    public function alwaysShouldRejectWhenHandlerRejectsForFulfilledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->resolve(1);
        $adapter->promise()
            ->always(static fn() => reject($exception))
            ->then(null, $mock);
    }
}
