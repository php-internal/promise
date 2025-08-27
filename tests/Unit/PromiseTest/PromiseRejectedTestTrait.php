<?php

declare(strict_types=1);

namespace React\Promise\Unit\PromiseTest;

use React\Promise\PromiseInterface;
use React\Promise\Unit\PromiseAdapter\PromiseAdapterInterface;

use function React\Promise\reject;
use function React\Promise\resolve;

trait PromiseRejectedTestTrait
{
    abstract public function getPromiseTestAdapter(?callable $canceller = null): PromiseAdapterInterface;

    /**
     * @test
     */
    public function rejectedPromiseShouldBeImmutable(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception1 = new \Exception();
        $exception2 = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception1));

        $adapter->reject($exception1);
        $adapter->reject($exception2);

        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
                $mock,
            );
    }

    /**
     * @test
     */
    public function rejectedPromiseShouldInvokeNewlyAddedCallback(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $adapter->reject($exception);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->then($this->expectCallableNever(), $mock);
    }

    /**
     * @test
     */
    public function shouldForwardUndefinedRejectionValue(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with(null);

        $adapter->reject(new \Exception());
        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
                static function (): void {
                    // Presence of rejection handler is enough to switch back
                    // to resolve mode, even though it returns undefined.
                    // The ONLY way to propagate a rejection is to re-throw or
                    // return a rejected promise;
                },
            )
            ->then(
                $mock,
                $this->expectCallableNever(),
            );
    }

    /**
     * @test
     */
    public function shouldSwitchFromErrbacksToCallbacksWhenErrbackDoesNotExplicitlyPropagate(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $adapter->reject(new \Exception());
        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
                static fn() => 2,
            )
            ->then(
                $mock,
                $this->expectCallableNever(),
            );
    }

    /**
     * @test
     */
    public function shouldSwitchFromErrbacksToCallbacksWhenErrbackReturnsAResolution(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $adapter->reject(new \Exception());
        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
                static fn() => resolve(2),
            )
            ->then(
                $mock,
                $this->expectCallableNever(),
            );
    }

    /**
     * @test
     */
    public function shouldPropagateRejectionsWhenErrbackThrows(): void
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

        $adapter->reject(new \Exception());
        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
                $mock,
            )
            ->then(
                $this->expectCallableNever(),
                $mock2,
            );
    }

    /**
     * @test
     */
    public function shouldPropagateRejectionsWhenErrbackReturnsARejection(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->reject(new \Exception());
        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
                static fn() => reject($exception),
            )
            ->then(
                $this->expectCallableNever(),
                $mock,
            );
    }

    /**
     * @test
     */
    public function catchShouldInvokeRejectionHandlerForRejectedPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->reject($exception);
        $adapter->promise()->catch($mock);
    }

    /**
     * @test
     */
    public function catchShouldInvokeNonTypeHintedRejectionHandlerIfReasonIsAnExceptionForRejectedPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->reject($exception);
        $adapter->promise()
            ->catch(static function ($reason) use ($mock): void {
                $mock($reason);
            });
    }

    /**
     * @test
     */
    public function catchShouldInvokeRejectionHandlerIfReasonMatchesTypehintForRejectedPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \InvalidArgumentException();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->reject($exception);
        $adapter->promise()
            ->catch(static function (\InvalidArgumentException $reason) use ($mock): void {
                $mock($reason);
            });
    }

    /**
     * @test
     */
    public function catchShouldNotInvokeRejectionHandlerIfReaonsDoesNotMatchTypehintForRejectedPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->expectCallableNever();

        $adapter->reject($exception);
        $adapter->promise()
            ->catch(static function (\InvalidArgumentException $reason) use ($mock): void {
                $mock($reason);
            })->then(null, $this->expectCallableOnce()); // avoid reporting unhandled rejection
    }

    /**
     * @test
     */
    public function finallyShouldNotSuppressRejectionForRejectedPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->reject($exception);
        $adapter->promise()
            ->finally(static function (): void {})
            ->then(null, $mock);
    }

    /**
     * @test
     */
    public function finallyShouldNotSuppressRejectionWhenHandlerReturnsANonPromiseForRejectedPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->reject($exception);
        $adapter->promise()
            ->finally(static fn(): int =>
                // @phpstan-ignore-line
                1)
            ->then(null, $mock);
    }

    /**
     * @test
     */
    public function finallyShouldNotSuppressRejectionWhenHandlerReturnsAPromiseForRejectedPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->reject($exception);
        $adapter->promise()
            ->finally(static fn(): PromiseInterface =>
                // @phpstan-ignore-line
                resolve(1))
            ->then(null, $mock);
    }

    /**
     * @test
     */
    public function finallyShouldRejectWhenHandlerThrowsForRejectedPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception1 = new \Exception();
        $exception2 = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception2));

        $adapter->reject($exception1);
        $adapter->promise()
            ->finally(static function () use ($exception2): void {
                throw $exception2;
            })
            ->then(null, $mock);
    }

    /**
     * @test
     */
    public function finallyShouldRejectWhenHandlerRejectsForRejectedPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception1 = new \Exception();
        $exception2 = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception2));

        $adapter->reject($exception1);
        $adapter->promise()
            ->finally(static fn() => reject($exception2))
            ->then(null, $mock);
    }

    /**
     * @test
     */
    public function cancelShouldHaveNoEffectForRejectedPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter($this->expectCallableNever());

        $adapter->reject(new \Exception());

        $adapter->promise()->cancel();
    }

    /**
     * @test
     * @deprecated
     */
    public function otherwiseShouldInvokeRejectionHandlerForRejectedPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->reject($exception);
        $adapter->promise()->otherwise($mock);
    }

    /**
     * @test
     * @deprecated
     */
    public function otherwiseShouldInvokeNonTypeHintedRejectionHandlerIfReasonIsAnExceptionForRejectedPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->reject($exception);
        $adapter->promise()
            ->otherwise(static function ($reason) use ($mock): void {
                $mock($reason);
            });
    }

    /**
     * @test
     * @deprecated
     */
    public function otherwiseShouldInvokeRejectionHandlerIfReasonMatchesTypehintForRejectedPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \InvalidArgumentException();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->reject($exception);
        $adapter->promise()
            ->otherwise(static function (\InvalidArgumentException $reason) use ($mock): void {
                $mock($reason);
            });
    }

    /**
     * @test
     * @deprecated
     */
    public function otherwiseShouldNotInvokeRejectionHandlerIfReaonsDoesNotMatchTypehintForRejectedPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->expectCallableNever();

        $adapter->reject($exception);
        $adapter->promise()
            ->otherwise(static function (\InvalidArgumentException $reason) use ($mock): void {
                $mock($reason);
            })->then(null, $this->expectCallableOnce()); // avoid reporting unhandled rejection
    }

    /**
     * @test
     * @deprecated
     */
    public function alwaysShouldNotSuppressRejectionForRejectedPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->reject($exception);
        $adapter->promise()
            ->always(static function (): void {})
            ->then(null, $mock);
    }

    /**
     * @test
     * @deprecated
     */
    public function alwaysShouldNotSuppressRejectionWhenHandlerReturnsANonPromiseForRejectedPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->reject($exception);
        $adapter->promise()
            ->finally(static fn(): int =>
                // @phpstan-ignore-line
                1)
            ->then(null, $mock);
    }

    /**
     * @test
     * @deprecated
     */
    public function alwaysShouldNotSuppressRejectionWhenHandlerReturnsAPromiseForRejectedPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->reject($exception);
        $adapter->promise()
            ->always(static fn(): PromiseInterface =>
                // @phpstan-ignore-line
                resolve(1))
            ->then(null, $mock);
    }

    /**
     * @test
     * @deprecated
     */
    public function alwaysShouldRejectWhenHandlerThrowsForRejectedPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception1 = new \Exception();
        $exception2 = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception2));

        $adapter->reject($exception1);
        $adapter->promise()
            ->always(static function () use ($exception2): void {
                throw $exception2;
            })
            ->then(null, $mock);
    }

    /**
     * @test
     * @deprecated
     */
    public function alwaysShouldRejectWhenHandlerRejectsForRejectedPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception1 = new \Exception();
        $exception2 = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception2));

        $adapter->reject($exception1);
        $adapter->promise()
            ->always(static fn() => reject($exception2))
            ->then(null, $mock);
    }
}
