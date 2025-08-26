<?php

declare(strict_types=1);

namespace React\Promise;

class FunctionMapTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldMapInputValuesArray(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([2, 4, 6]));

        map(
            [1, 2, 3],
            $this->mapper(),
        )->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldMapInputPromisesArray(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([2, 4, 6]));

        map(
            [resolve(1), resolve(2), resolve(3)],
            $this->mapper(),
        )->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldMapMixedInputArray(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([2, 4, 6]));

        map(
            [1, resolve(2), 3],
            $this->mapper(),
        )->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldMapInputWhenMapperReturnsAPromise(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([2, 4, 6]));

        map(
            [1, 2, 3],
            $this->promiseMapper(),
        )->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldAcceptAPromiseForAnArray(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([2, 4, 6]));

        map(
            resolve([1, resolve(2), 3]),
            $this->mapper(),
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

        map(
            resolve(1),
            $this->mapper(),
        )->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldPreserveTheOrderOfArrayWhenResolvingAsyncPromises(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([2, 4, 6]));

        $deferred = new Deferred();

        map(
            [resolve(1), $deferred->promise(), resolve(3)],
            $this->mapper(),
        )->then($mock);

        $deferred->resolve(2);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldRejectWhenInputContainsRejection(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        map(
            [resolve(1), reject(2), resolve(3)],
            $this->mapper(),
        )->then($this->expectCallableNever(), $mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldRejectWhenInputPromiseRejects(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(null));

        map(
            reject(),
            $this->mapper(),
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

        map(
            $mock,
            $this->mapper(),
        )->cancel();
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

        map(
            [$mock1, $mock2],
            $this->mapper(),
        )->cancel();
    }

    protected function mapper()
    {
        return static function ($val) {
            return $val * 2;
        };
    }

    protected function promiseMapper()
    {
        return static function ($val) {
            return resolve($val * 2);
        };
    }
}
