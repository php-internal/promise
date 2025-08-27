<?php

declare(strict_types=1);

namespace React\Promise\Tests\Unit\PromiseTest;

use PHPUnit\Framework\Attributes\Test;
use React\Promise\PromiseInterface;
use React\Promise\Tests\Unit\PromiseAdapter\PromiseAdapterInterface;

use function React\Promise\reject;
use function React\Promise\resolve;

trait ResolveTestTrait
{
    abstract public function getPromiseTestAdapter(?callable $canceller = null): PromiseAdapterInterface;

    #[Test]
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

    #[Test]
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

        $adapter->resolve(resolve(1));
    }

    #[Test]
    public function resolveShouldRejectWhenResolvedWithRejectedPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->then($this->expectCallableNever(), $mock);

        $adapter->resolve(reject($exception));
    }

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
    public function finallyShouldNotSuppressValue(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $value = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($value));

        $adapter->promise()
            ->finally(static function (): void {})
            ->then($mock);

        $adapter->resolve($value);
    }

    #[Test]
    public function finallyShouldNotSuppressValueWhenHandlerReturnsANonPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $value = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($value));

        $adapter->promise()
            // @phpstan-ignore-line
            ->finally(static fn(): int => 1)
            ->then($mock);

        $adapter->resolve($value);
    }

    #[Test]
    public function finallyShouldNotSuppressValueWhenHandlerReturnsAPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $value = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($value));

        $adapter->promise()
            // @phpstan-ignore-line
            ->finally(static fn(): PromiseInterface =>
                resolve(1))
            ->then($mock);

        $adapter->resolve($value);
    }

    #[Test]
    public function finallyShouldRejectWhenHandlerThrowsForFulfillment(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->finally(static function () use ($exception): void {
                throw $exception;
            })
            ->then(null, $mock);

        $adapter->resolve(1);
    }

    #[Test]
    public function finallyShouldRejectWhenHandlerRejectsForFulfillment(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->finally(static fn() => reject($exception))
            ->then(null, $mock);

        $adapter->resolve(1);
    }
}
