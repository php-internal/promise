<?php

declare(strict_types=1);

namespace React\Promise\Tests\Unit\PromiseTest;

use PHPUnit\Framework\Attributes\Test;
use React\Promise\PromiseInterface;
use React\Promise\Tests\Unit\PromiseAdapter\PromiseAdapterInterface;

use function React\Promise\reject;
use function React\Promise\resolve;

trait RejectTestTrait
{
    abstract public function getPromiseTestAdapter(?callable $canceller = null): PromiseAdapterInterface;

    #[Test]
    public function rejectShouldRejectWithAnException(): void
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

        $adapter->reject($exception);
    }

    #[Test]
    public function rejectShouldForwardReasonWhenCallbackIsNull(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
            )
            ->then(
                $this->expectCallableNever(),
                $mock,
            );

        $adapter->reject($exception);
    }

    #[Test]
    public function rejectShouldMakePromiseImmutable(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception1 = new \Exception();
        $exception2 = new \Exception();
        $exception3 = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception1));

        $adapter->promise()
            ->then(null, static function (\Throwable $value) use ($exception3, $adapter): PromiseInterface {
                $adapter->reject($exception3);

                return reject($value);
            })
            ->then(
                $this->expectCallableNever(),
                $mock,
            );

        $adapter->reject($exception1);
        $adapter->reject($exception2);
    }

    #[Test]
    public function rejectShouldInvokeCatchHandler(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->catch($mock);

        $adapter->reject($exception);
    }

    #[Test]
    public function finallyShouldNotSuppressRejection(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->finally(static function (): void {})
            ->then(null, $mock);

        $adapter->reject($exception);
    }

    #[Test]
    public function finallyShouldNotSuppressRejectionWhenHandlerReturnsANonPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            // @phpstan-ignore-line
            ->finally(static fn(): int => 1)
            ->then(null, $mock);

        $adapter->reject($exception);
    }

    #[Test]
    public function finallyShouldNotSuppressRejectionWhenHandlerReturnsAPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            // @phpstan-ignore-line
            ->finally(static fn(): PromiseInterface => resolve(1))
            ->then(null, $mock);

        $adapter->reject($exception);
    }

    #[Test]
    public function finallyShouldRejectWhenHandlerThrowsForRejection(): void
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

        $adapter->reject($exception);
    }

    #[Test]
    public function finallyShouldRejectWhenHandlerRejectsForRejection(): void
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

        $adapter->reject($exception);
    }
}
