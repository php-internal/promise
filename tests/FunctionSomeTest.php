<?php

declare(strict_types=1);

namespace React\Promise;

use React\Promise\Exception\LengthException;

class FunctionSomeTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldRejectWithLengthExceptionWithEmptyInputArray(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with(
                $this->callback(static function ($exception) {
                    return $exception instanceof LengthException &&
                           $exception->getMessage() === 'Input array must contain at least 1 item but contains only 0 items.';
                }),
            );

        some(
            [],
            1,
        )->then($this->expectCallableNever(), $mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldRejectWithLengthExceptionWithInputArrayContainingNotEnoughItems(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with(
                $this->callback(static function ($exception) {
                    return $exception instanceof LengthException &&
                           $exception->getMessage() === 'Input array must contain at least 4 items but contains only 3 items.';
                }),
            );

        some(
            [1, 2, 3],
            4,
        )->then($this->expectCallableNever(), $mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldResolveToEmptyArrayWithNonArrayInput(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([]));

        some(
            null,
            1,
        )->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldResolveValuesArray(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([1, 2]));

        some(
            [1, 2, 3],
            2,
        )->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldResolvePromisesArray(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([1, 2]));

        some(
            [resolve(1), resolve(2), resolve(3)],
            2,
        )->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldResolveSparseArrayInput(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([null, 1]));

        some(
            [null, 1, null, 2, 3],
            2,
        )->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldRejectIfAnyInputPromiseRejectsBeforeDesiredNumberOfInputsAreResolved(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([1 => 2, 2 => 3]));

        some(
            [resolve(1), reject(2), reject(3)],
            2,
        )->then($this->expectCallableNever(), $mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldAcceptAPromiseForAnArray(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([1, 2]));

        some(
            resolve([1, 2, 3]),
            2,
        )->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldResolveWithEmptyArrayIfHowManyIsLessThanOne(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([]));

        some(
            [1],
            0,
        )->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldResolveToEmptyArrayWhenInputPromiseDoesNotResolveToArray(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([]));

        some(
            resolve(1),
            1,
        )->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldRejectWhenInputPromiseRejects(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(null));

        some(
            reject(),
            1,
        )->then($this->expectCallableNever(), $mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldCancelInputPromise(): void
    {
        $mock = $this
            ->getMockBuilder('React\Promise\CancellablePromiseInterface')
            ->getMock();
        $mock
            ->expects($this->once())
            ->method('cancel');

        some($mock, 1)->cancel();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldCancelInputArrayPromises(): void
    {
        $mock1 = $this
            ->getMockBuilder('React\Promise\CancellablePromiseInterface')
            ->getMock();
        $mock1
            ->expects($this->once())
            ->method('cancel');

        $mock2 = $this
            ->getMockBuilder('React\Promise\CancellablePromiseInterface')
            ->getMock();
        $mock2
            ->expects($this->once())
            ->method('cancel');

        some([$mock1, $mock2], 1)->cancel();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldNotCancelOtherPendingInputArrayPromisesIfEnoughPromisesFulfill(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->never())
            ->method('__invoke');

        $deferred = new Deferred($mock);
        $deferred->resolve();

        $mock2 = $this
            ->getMockBuilder('React\Promise\CancellablePromiseInterface')
            ->getMock();
        $mock2
            ->expects($this->never())
            ->method('cancel');

        some([$deferred->promise(), $mock2], 1);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldNotCancelOtherPendingInputArrayPromisesIfEnoughPromisesReject(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->never())
            ->method('__invoke');

        $deferred = new Deferred($mock);
        $deferred->reject();

        $mock2 = $this
            ->getMockBuilder('React\Promise\CancellablePromiseInterface')
            ->getMock();
        $mock2
            ->expects($this->never())
            ->method('cancel');

        some([$deferred->promise(), $mock2], 2);
    }
}
