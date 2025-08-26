<?php

declare(strict_types=1);

namespace React\Promise\Tests\Unit\PromiseTest;

trait NotifyTestTrait
{
    /**
     * @return \React\Promise\Tests\Unit\PromiseAdapter\PromiseAdapterInterface
     */
    abstract public function getPromiseTestAdapter(?callable $canceller = null);

    #[\PHPUnit\Framework\Attributes\Test]
    public function notifyShouldProgress(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $sentinel = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($sentinel);

        $adapter->promise()
            ->then($this->expectCallableNever(), $this->expectCallableNever(), $mock);

        $adapter->notify($sentinel);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function notifyShouldPropagateProgressToDownstreamPromises(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $sentinel = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->returnArgument(0));

        $mock2 = $this->createCallableMock();
        $mock2
            ->expects($this->once())
            ->method('__invoke')
            ->with($sentinel);

        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $mock,
            )
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $mock2,
            );

        $adapter->notify($sentinel);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function notifyShouldPropagateTransformedProgressToDownstreamPromises(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $sentinel = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->returnValue($sentinel));

        $mock2 = $this->createCallableMock();
        $mock2
            ->expects($this->once())
            ->method('__invoke')
            ->with($sentinel);

        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $mock,
            )
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $mock2,
            );

        $adapter->notify(1);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function notifyShouldPropagateCaughtExceptionValueAsProgress(): void
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

        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $mock,
            )
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $mock2,
            );

        $adapter->notify(1);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function notifyShouldForwardProgressEventsWhenIntermediaryCallbackTiedToAResolvedPromiseReturnsAPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();
        $adapter2 = $this->getPromiseTestAdapter();

        $promise2 = $adapter2->promise();

        $sentinel = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($sentinel);

        // resolve BEFORE attaching progress handler
        $adapter->resolve();

        $adapter->promise()
            ->then(static fn() => $promise2)
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $mock,
            );

        $adapter2->notify($sentinel);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function notifyShouldForwardProgressEventsWhenIntermediaryCallbackTiedToAnUnresolvedPromiseReturnsAPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();
        $adapter2 = $this->getPromiseTestAdapter();

        $promise2 = $adapter2->promise();

        $sentinel = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($sentinel);

        $adapter->promise()
            ->then(static fn() => $promise2)
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $mock,
            );

        // resolve AFTER attaching progress handler
        $adapter->resolve();
        $adapter2->notify($sentinel);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function notifyShouldForwardProgressWhenResolvedWithAnotherPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();
        $adapter2 = $this->getPromiseTestAdapter();

        $sentinel = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->returnValue($sentinel));

        $mock2 = $this->createCallableMock();
        $mock2
            ->expects($this->once())
            ->method('__invoke')
            ->with($sentinel);

        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $mock,
            )
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $mock2,
            );

        $adapter->resolve($adapter2->promise());
        $adapter2->notify($sentinel);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function notifyShouldAllowResolveAfterProgress(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock->expects($this->exactly(2))->method('__invoke')
            ->with($this->callback(static function ($arg) {
                static $calls = 0;
                $calls++;
                if ($calls === 1) {
                    return $arg === 1;
                }
                if ($calls === 2) {
                    return $arg === 2;
                }
                return false;
            }));

        $adapter->promise()
            ->then(
                $mock,
                $this->expectCallableNever(),
                $mock,
            );

        $adapter->notify(1);
        $adapter->resolve(2);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function notifyShouldAllowRejectAfterProgress(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock->expects($this->exactly(2))->method('__invoke')
            ->with($this->callback(static function ($arg) {
                static $calls = 0;
                $calls++;
                if ($calls === 1) {
                    return $arg === 1;
                }
                if ($calls === 2) {
                    return $arg === 2;
                }
                return false;
            }));

        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
                $mock,
                $mock,
            );

        $adapter->notify(1);
        $adapter->reject(2);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function notifyShouldReturnSilentlyOnProgressWhenAlreadyRejected(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->reject(1);

        $this->assertNull($adapter->notify());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function notifyShouldInvokeProgressHandler(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()->progress($mock);
        $adapter->notify(1);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function notifyShouldInvokeProgressHandlerFromDone(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $this->assertNull($adapter->promise()->done(null, null, $mock));
        $adapter->notify(1);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function notifyShouldThrowExceptionThrownProgressHandlerFromDone(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException('\Exception', 'UnhandledRejectionException');

        $this->assertNull($adapter->promise()->done(null, null, static function (): void {
            throw new \Exception('UnhandledRejectionException');
        }));
        $adapter->notify(1);
    }
}
