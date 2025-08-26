<?php

declare(strict_types=1);

namespace React\Promise\Tests\Unit\PromiseTest;

trait PromiseFulfilledTestTrait
{
    /**
     * @return \React\Promise\Tests\Unit\PromiseAdapter\PromiseAdapterInterface
     */
    abstract public function getPromiseTestAdapter(?callable $canceller = null);

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
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
                static fn($val) => \React\Promise\resolve($val + 1),
                $this->expectCallableNever(),
            )
            ->then(
                $mock,
                $this->expectCallableNever(),
            );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function thenShouldSwitchFromCallbacksToErrbacksWhenCallbackReturnsARejection(): void
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
                static fn($val) => \React\Promise\reject($val + 1),
                $this->expectCallableNever(),
            )
            ->then(
                $this->expectCallableNever(),
                $mock,
            );
    }

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
    public function cancelShouldReturnNullForFulfilledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->resolve();

        $this->assertNull($adapter->promise()->cancel());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function cancelShouldHaveNoEffectForFulfilledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter($this->expectCallableNever());

        $adapter->resolve();

        $adapter->promise()->cancel();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function doneShouldInvokeFulfillmentHandlerForFulfilledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->resolve(1);
        $this->assertNull($adapter->promise()->done($mock));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function doneShouldThrowExceptionThrownFulfillmentHandlerForFulfilledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException('\Exception', 'UnhandledRejectionException');

        $adapter->resolve(1);
        $this->assertNull($adapter->promise()->done(static function (): void {
            throw new \Exception('UnhandledRejectionException');
        }));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function doneShouldThrowUnhandledRejectionExceptionWhenFulfillmentHandlerRejectsForFulfilledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException(\React\Promise\UnhandledRejectionException::class);

        $adapter->resolve(1);
        $this->assertNull($adapter->promise()->done(static fn() => \React\Promise\reject()));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function otherwiseShouldNotInvokeRejectionHandlerForFulfilledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->resolve(1);
        $adapter->promise()->otherwise($this->expectCallableNever());
    }

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
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
            ->always(static fn() => 1)
            ->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
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
            ->always(static fn() => \React\Promise\resolve(1))
            ->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
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
            ->always(static fn() => \React\Promise\reject($exception))
            ->then(null, $mock);
    }
}
