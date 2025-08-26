<?php

declare(strict_types=1);

namespace React\Promise\Tests\Unit;

use React\Promise\Deferred;
use React\Promise\Exception\LengthException;

use function React\Promise\any;
use function React\Promise\reject;
use function React\Promise\resolve;
use function React\Promise\some;

class FunctionAnyTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldRejectWithLengthExceptionWithEmptyInputArray(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with(
                $this->callback(static fn($exception) => $exception instanceof LengthException &&
                       $exception->getMessage() === 'Input array must contain at least 1 item but contains only 0 items.'),
            );

        any([])
            ->then($this->expectCallableNever(), $mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldResolveToNullWithNonArrayInput(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(null));

        any(null)
            ->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldResolveWithAnInputValue(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        any([1, 2, 3])
            ->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldResolveWithAPromisedInputValue(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        any([resolve(1), resolve(2), resolve(3)])
            ->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldRejectWithAllRejectedInputValuesIfAllInputsAreRejected(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([0 => 1, 1 => 2, 2 => 3]));

        any([reject(1), reject(2), reject(3)])
            ->then($this->expectCallableNever(), $mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldResolveWhenFirstInputPromiseResolves(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        any([resolve(1), reject(2), reject(3)])
            ->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldAcceptAPromiseForAnArray(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        any(resolve([1, 2, 3]))
            ->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldResolveToNullArrayWhenInputPromiseDoesNotResolveToArray(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(null));

        any(resolve(1))
            ->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldNotRelyOnArryIndexesWhenUnwrappingToASingleResolutionValue(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $d1 = new Deferred();
        $d2 = new Deferred();

        any(['abc' => $d1->promise(), 1 => $d2->promise()])
            ->then($mock);

        $d2->resolve(2);
        $d1->resolve(1);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldRejectWhenInputPromiseRejects(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(null));

        any(reject())
            ->then($this->expectCallableNever(), $mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldCancelInputPromise(): void
    {
        $mock = $this
            ->getMockBuilder(\React\Promise\CancellablePromiseInterface::class)
            ->getMock();
        $mock
            ->expects($this->once())
            ->method('cancel');

        any($mock)->cancel();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldCancelInputArrayPromises(): void
    {
        $mock1 = $this
            ->getMockBuilder(\React\Promise\CancellablePromiseInterface::class)
            ->getMock();
        $mock1
            ->expects($this->once())
            ->method('cancel');

        $mock2 = $this
            ->getMockBuilder(\React\Promise\CancellablePromiseInterface::class)
            ->getMock();
        $mock2
            ->expects($this->once())
            ->method('cancel');

        any([$mock1, $mock2])->cancel();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldNotCancelOtherPendingInputArrayPromisesIfOnePromiseFulfills(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->never())
            ->method('__invoke');


        $deferred = new Deferred($mock);
        $deferred->resolve();

        $mock2 = $this
            ->getMockBuilder(\React\Promise\CancellablePromiseInterface::class)
            ->getMock();
        $mock2
            ->expects($this->never())
            ->method('cancel');

        some([$deferred->promise(), $mock2], 1)->cancel();
    }
}
