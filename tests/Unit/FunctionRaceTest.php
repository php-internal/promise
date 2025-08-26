<?php

declare(strict_types=1);

namespace React\Promise\Tests\Unit;

use React\Promise\Deferred;

use function React\Promise\race;
use function React\Promise\reject;
use function React\Promise\resolve;

class FunctionRaceTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldResolveEmptyInput(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(null));

        race(
            [],
        )->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldResolveValuesArray(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        race(
            [1, 2, 3],
        )->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldResolvePromisesArray(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $d1 = new Deferred();
        $d2 = new Deferred();
        $d3 = new Deferred();

        race(
            [$d1->promise(), $d2->promise(), $d3->promise()],
        )->then($mock);

        $d2->resolve(2);

        $d1->resolve(1);
        $d3->resolve(3);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldResolveSparseArrayInput(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(null));

        race(
            [null, 1, null, 2, 3],
        )->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldRejectIfFirstSettledPromiseRejects(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $d1 = new Deferred();
        $d2 = new Deferred();
        $d3 = new Deferred();

        race(
            [$d1->promise(), $d2->promise(), $d3->promise()],
        )->then($this->expectCallableNever(), $mock);

        $d2->reject(2);

        $d1->resolve(1);
        $d3->resolve(3);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldAcceptAPromiseForAnArray(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        race(
            resolve([1, 2, 3]),
        )->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldResolveToNullWhenInputPromiseDoesNotResolveToArray(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(null));

        race(
            resolve(1),
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

        race(
            reject(),
        )->then($this->expectCallableNever(), $mock);
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

        race($mock)->cancel();
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

        race([$mock1, $mock2])->cancel();
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

        race([$deferred->promise(), $mock2])->cancel();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldNotCancelOtherPendingInputArrayPromisesIfOnePromiseRejects(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->never())
            ->method('__invoke');

        $deferred = new Deferred($mock);
        $deferred->reject();

        $mock2 = $this
            ->getMockBuilder(\React\Promise\CancellablePromiseInterface::class)
            ->getMock();
        $mock2
            ->expects($this->never())
            ->method('cancel');

        race([$deferred->promise(), $mock2])->cancel();
    }
}
