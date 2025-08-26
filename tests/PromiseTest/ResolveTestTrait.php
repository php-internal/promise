<?php

declare(strict_types=1);

namespace React\Promise\PromiseTest;

use React\Promise;

trait ResolveTestTrait
{
    /**
     * @return \React\Promise\PromiseAdapter\PromiseAdapterInterface
     */
    abstract public function getPromiseTestAdapter(?callable $canceller = null);

    #[\PHPUnit\Framework\Attributes\Test]
    public function resolveShouldResolve(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then($mock);

        $adapter->resolve(1);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function resolveShouldResolveWithPromisedValue(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then($mock);

        $adapter->resolve(Promise\resolve(1));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function resolveShouldRejectWhenResolvedWithRejectedPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then($this->expectCallableNever(), $mock);

        $adapter->resolve(Promise\reject(1));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function resolveShouldForwardValueWhenCallbackIsNull(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then(
                null,
                $this->expectCallableNever(),
            )
            ->then(
                $mock,
                $this->expectCallableNever(),
            );

        $adapter->resolve(1);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function resolveShouldMakePromiseImmutable(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then(static function ($value) use ($adapter) {
                $adapter->resolve(3);

                return $value;
            })
            ->then(
                $mock,
                $this->expectCallableNever(),
            );

        $adapter->resolve(1);
        $adapter->resolve(2);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function resolveShouldRejectWhenResolvedWithItself(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with(new \LogicException('Cannot resolve a promise with itself.'));

        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
                $mock,
            );

        $adapter->resolve($adapter->promise());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function resolveShouldRejectWhenResolvedWithAPromiseWhichFollowsItself(): void
    {
        $adapter1 = $this->getPromiseTestAdapter();
        $adapter2 = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with(new \LogicException('Cannot resolve a promise with itself.'));

        $promise1 = $adapter1->promise();

        $promise2 = $adapter2->promise();

        $promise2->then(
            $this->expectCallableNever(),
            $mock,
        );

        $adapter1->resolve($promise2);
        $adapter2->resolve($promise1);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function doneShouldInvokeFulfillmentHandler(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $this->assertNull($adapter->promise()->done($mock));
        $adapter->resolve(1);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function doneShouldThrowExceptionThrownFulfillmentHandler(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException('\Exception', 'UnhandledRejectionException');

        $this->assertNull($adapter->promise()->done(static function (): void {
            throw new \Exception('UnhandledRejectionException');
        }));
        $adapter->resolve(1);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function doneShouldThrowUnhandledRejectionExceptionWhenFulfillmentHandlerRejects(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException(\React\Promise\UnhandledRejectionException::class);

        $this->assertNull($adapter->promise()->done(static function () {
            return \React\Promise\reject();
        }));
        $adapter->resolve(1);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function alwaysShouldNotSuppressValue(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $value = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($value));

        $adapter->promise()
            ->always(static function (): void {})
            ->then($mock);

        $adapter->resolve($value);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function alwaysShouldNotSuppressValueWhenHandlerReturnsANonPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $value = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($value));

        $adapter->promise()
            ->always(static function () {
                return 1;
            })
            ->then($mock);

        $adapter->resolve($value);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function alwaysShouldNotSuppressValueWhenHandlerReturnsAPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $value = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($value));

        $adapter->promise()
            ->always(static function () {
                return \React\Promise\resolve(1);
            })
            ->then($mock);

        $adapter->resolve($value);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function alwaysShouldRejectWhenHandlerThrowsForFulfillment(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->always(static function () use ($exception): void {
                throw $exception;
            })
            ->then(null, $mock);

        $adapter->resolve(1);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function alwaysShouldRejectWhenHandlerRejectsForFulfillment(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->always(static function () use ($exception) {
                return \React\Promise\reject($exception);
            })
            ->then(null, $mock);

        $adapter->resolve(1);
    }
}
