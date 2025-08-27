<?php

namespace React\Promise\PromiseTest;

use React\Promise\PromiseAdapter\PromiseAdapterInterface;
use React\Promise\PromiseInterface;

use function React\Promise\reject;
use function React\Promise\resolve;

trait RejectTestTrait
{
    abstract public function getPromiseTestAdapter(?callable $canceller = null): PromiseAdapterInterface;

    /**
     * @test
     */
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

    /**
     * @test
     */
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

    /**
     * @test
     */
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
            ->then(null, function (\Throwable $value) use ($exception3, $adapter): PromiseInterface {
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

    /**
     * @test
     */
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

    /**
     * @test
     */
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
            ->finally(function (): void {})
            ->then(null, $mock);

        $adapter->reject($exception);
    }

    /**
     * @test
     */
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
            ->finally(fn(): int => 1)
            ->then(null, $mock);

        $adapter->reject($exception);
    }

    /**
     * @test
     */
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
            ->finally(fn(): PromiseInterface => resolve(1))
            ->then(null, $mock);

        $adapter->reject($exception);
    }

    /**
     * @test
     */
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
            ->finally(function () use ($exception): void {
                throw $exception;
            })
            ->then(null, $mock);

        $adapter->reject($exception);
    }

    /**
     * @test
     */
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
            ->finally(fn() => reject($exception))
            ->then(null, $mock);

        $adapter->reject($exception);
    }
}
