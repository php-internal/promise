<?php

declare(strict_types=1);

namespace React\Promise\Tests\Unit\PromiseTest;

use React\Promise;
use React\Promise\Deferred;

trait RejectTestTrait
{
    /**
     * @return \React\Promise\Tests\Unit\PromiseAdapter\PromiseAdapterInterface
     */
    abstract public function getPromiseTestAdapter(?callable $canceller = null);

    #[\PHPUnit\Framework\Attributes\Test]
    public function rejectShouldRejectWithAnImmediateValue(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then($this->expectCallableNever(), $mock);

        $adapter->reject(1);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function rejectShouldRejectWithFulfilledPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then($this->expectCallableNever(), $mock);

        $adapter->reject(Promise\resolve(1));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function rejectShouldRejectWithRejectedPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then($this->expectCallableNever(), $mock);

        $adapter->reject(Promise\reject(1));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function rejectShouldForwardReasonWhenCallbackIsNull(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
            )
            ->then(
                $this->expectCallableNever(),
                $mock,
            );

        $adapter->reject(1);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function rejectShouldMakePromiseImmutable(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then(null, static function ($value) use ($adapter) {
                $adapter->reject(3);

                return Promise\reject($value);
            })
            ->then(
                $this->expectCallableNever(),
                $mock,
            );

        $adapter->reject(1);
        $adapter->reject(2);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function notifyShouldInvokeOtherwiseHandler(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->otherwise($mock);

        $adapter->reject(1);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function doneShouldInvokeRejectionHandler(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $this->assertNull($adapter->promise()->done(null, $mock));
        $adapter->reject(1);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function doneShouldThrowExceptionThrownByRejectionHandler(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException('\Exception', 'UnhandledRejectionException');

        $this->assertNull($adapter->promise()->done(null, static function (): void {
            throw new \Exception('UnhandledRejectionException');
        }));
        $adapter->reject(1);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function doneShouldThrowUnhandledRejectionExceptionWhenRejectedWithNonException(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException(\React\Promise\UnhandledRejectionException::class);

        $this->assertNull($adapter->promise()->done());
        $adapter->reject(1);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function doneShouldThrowUnhandledRejectionExceptionWhenRejectionHandlerRejects(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException(\React\Promise\UnhandledRejectionException::class);

        $this->assertNull($adapter->promise()->done(null, static fn() => \React\Promise\reject()));
        $adapter->reject(1);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function doneShouldThrowRejectionExceptionWhenRejectionHandlerRejectsWithException(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException('\Exception', 'UnhandledRejectionException');

        $this->assertNull($adapter->promise()->done(null, static fn() => \React\Promise\reject(new \Exception('UnhandledRejectionException'))));
        $adapter->reject(1);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function doneShouldThrowUnhandledRejectionExceptionWhenRejectionHandlerRetunsPendingPromiseWhichRejectsLater(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException(\React\Promise\UnhandledRejectionException::class);

        $d = new Deferred();
        $promise = $d->promise();

        $this->assertNull($adapter->promise()->done(null, static fn() => $promise));
        $adapter->reject(1);
        $d->reject(1);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function doneShouldThrowExceptionProvidedAsRejectionValue(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException('\Exception', 'UnhandledRejectionException');

        $this->assertNull($adapter->promise()->done());
        $adapter->reject(new \Exception('UnhandledRejectionException'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function doneShouldThrowWithDeepNestingPromiseChains(): void
    {
        $this->setExpectedException('\Exception', 'UnhandledRejectionException');

        $exception = new \Exception('UnhandledRejectionException');

        $d = new Deferred();

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

        $d->resolve();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function doneShouldRecoverWhenRejectionHandlerCatchesException(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->assertNull($adapter->promise()->done(null, static function (\Exception $e): void {}));
        $adapter->reject(new \Exception('UnhandledRejectionException'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function alwaysShouldNotSuppressRejection(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->always(static function (): void {})
            ->then(null, $mock);

        $adapter->reject($exception);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function alwaysShouldNotSuppressRejectionWhenHandlerReturnsANonPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->always(static fn() => 1)
            ->then(null, $mock);

        $adapter->reject($exception);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function alwaysShouldNotSuppressRejectionWhenHandlerReturnsAPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->always(static fn() => \React\Promise\resolve(1))
            ->then(null, $mock);

        $adapter->reject($exception);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function alwaysShouldRejectWhenHandlerThrowsForRejection(): void
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

        $adapter->reject($exception);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function alwaysShouldRejectWhenHandlerRejectsForRejection(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->always(static fn() => \React\Promise\reject($exception))
            ->then(null, $mock);

        $adapter->reject($exception);
    }
}
