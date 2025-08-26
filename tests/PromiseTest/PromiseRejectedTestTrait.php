<?php

declare(strict_types=1);

namespace React\Promise\PromiseTest;

use React\Promise\Deferred;
use React\Promise\UnhandledRejectionException;

trait PromiseRejectedTestTrait
{
    /**
     * @return \React\Promise\PromiseAdapter\PromiseAdapterInterface
     */
    abstract public function getPromiseTestAdapter(?callable $canceller = null);

    #[\PHPUnit\Framework\Attributes\Test]
    public function rejectedPromiseShouldBeImmutable(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->reject(1);
        $adapter->reject(2);

        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
                $mock,
            );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function rejectedPromiseShouldInvokeNewlyAddedCallback(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->reject(1);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then($this->expectCallableNever(), $mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldForwardUndefinedRejectionValue(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with(null);

        $adapter->reject(1);
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

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldSwitchFromErrbacksToCallbacksWhenErrbackDoesNotExplicitlyPropagate(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $adapter->reject(1);
        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
                static function ($val) {
                    return $val + 1;
                },
            )
            ->then(
                $mock,
                $this->expectCallableNever(),
            );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldSwitchFromErrbacksToCallbacksWhenErrbackReturnsAResolution(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $adapter->reject(1);
        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
                static function ($val) {
                    return \React\Promise\resolve($val + 1);
                },
            )
            ->then(
                $mock,
                $this->expectCallableNever(),
            );
    }

    #[\PHPUnit\Framework\Attributes\Test]
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

        $adapter->reject(1);
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

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldPropagateRejectionsWhenErrbackReturnsARejection(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $adapter->reject(1);
        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
                static function ($val) {
                    return \React\Promise\reject($val + 1);
                },
            )
            ->then(
                $this->expectCallableNever(),
                $mock,
            );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function doneShouldInvokeRejectionHandlerForRejectedPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->reject(1);
        $this->assertNull($adapter->promise()->done(null, $mock));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function doneShouldThrowExceptionThrownByRejectionHandlerForRejectedPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException('\Exception', 'UnhandledRejectionException');

        $adapter->reject(1);
        $this->assertNull($adapter->promise()->done(null, static function (): void {
            throw new \Exception('UnhandledRejectionException');
        }));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function doneShouldThrowUnhandledRejectionExceptionWhenRejectedWithNonExceptionForRejectedPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException(\React\Promise\UnhandledRejectionException::class);

        $adapter->reject(1);
        $this->assertNull($adapter->promise()->done());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function unhandledRejectionExceptionThrownByDoneHoldsRejectionValue(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $expected = new \stdClass();

        $adapter->reject($expected);

        try {
            $adapter->promise()->done();
        } catch (UnhandledRejectionException $e) {
            $this->assertSame($expected, $e->getReason());
            return;
        }

        $this->fail();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function doneShouldThrowUnhandledRejectionExceptionWhenRejectionHandlerRejectsForRejectedPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException(\React\Promise\UnhandledRejectionException::class);

        $adapter->reject(1);
        $this->assertNull($adapter->promise()->done(null, static function () {
            return \React\Promise\reject();
        }));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function doneShouldThrowRejectionExceptionWhenRejectionHandlerRejectsWithExceptionForRejectedPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException('\Exception', 'UnhandledRejectionException');

        $adapter->reject(1);
        $this->assertNull($adapter->promise()->done(null, static function () {
            return \React\Promise\reject(new \Exception('UnhandledRejectionException'));
        }));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function doneShouldThrowExceptionProvidedAsRejectionValueForRejectedPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException('\Exception', 'UnhandledRejectionException');

        $adapter->reject(new \Exception('UnhandledRejectionException'));
        $this->assertNull($adapter->promise()->done());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function doneShouldThrowWithDeepNestingPromiseChainsForRejectedPromise(): void
    {
        $this->setExpectedException('\Exception', 'UnhandledRejectionException');

        $exception = new \Exception('UnhandledRejectionException');

        $d = new Deferred();
        $d->resolve();

        $result = \React\Promise\resolve(\React\Promise\resolve($d->promise()->then(static function () use ($exception) {
            $d = new Deferred();
            $d->resolve();

            return \React\Promise\resolve($d->promise()->then(static function (): void {}))->then(
                static function () use ($exception): void {
                    throw $exception;
                },
            );
        })));

        $result->done();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function doneShouldRecoverWhenRejectionHandlerCatchesExceptionForRejectedPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->reject(new \Exception('UnhandledRejectionException'));
        $this->assertNull($adapter->promise()->done(null, static function (\Exception $e): void {}));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function otherwiseShouldInvokeRejectionHandlerForRejectedPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->reject(1);
        $adapter->promise()->otherwise($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
    public function otherwiseShouldNotInvokeRejectionHandlerIfReaonsDoesNotMatchTypehintForRejectedPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->expectCallableNever();

        $adapter->reject($exception);
        $adapter->promise()
            ->otherwise(static function (\InvalidArgumentException $reason) use ($mock): void {
                $mock($reason);
            });
    }

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
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
            ->always(static function () {
                return 1;
            })
            ->then(null, $mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
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
            ->always(static function () {
                return \React\Promise\resolve(1);
            })
            ->then(null, $mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
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
            ->always(static function () use ($exception2) {
                return \React\Promise\reject($exception2);
            })
            ->then(null, $mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function cancelShouldReturnNullForRejectedPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->reject();

        $this->assertNull($adapter->promise()->cancel());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function cancelShouldHaveNoEffectForRejectedPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter($this->expectCallableNever());

        $adapter->reject();

        $adapter->promise()->cancel();
    }
}
