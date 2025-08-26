<?php

declare(strict_types=1);

namespace React\Promise;

class FunctionAllTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldResolveEmptyInput(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([]));

        all([])
            ->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldResolveValuesArray(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([1, 2, 3]));

        all([1, 2, 3])
            ->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldResolvePromisesArray(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([1, 2, 3]));

        all([resolve(1), resolve(2), resolve(3)])
            ->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldResolveSparseArrayInput(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([null, 1, null, 1, 1]));

        all([null, 1, null, 1, 1])
            ->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldRejectIfAnyInputPromiseRejects(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        all([resolve(1), reject(2), resolve(3)])
            ->then($this->expectCallableNever(), $mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldAcceptAPromiseForAnArray(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([1, 2, 3]));

        all(resolve([1, 2, 3]))
            ->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldResolveToEmptyArrayWhenInputPromiseDoesNotResolveToArray(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([]));

        all(resolve(1))
            ->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldPreserveTheOrderOfArrayWhenResolvingAsyncPromises(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([1, 2, 3]));

        $deferred = new Deferred();

        all([resolve(1), $deferred->promise(), resolve(3)])
            ->then($mock);

        $deferred->resolve(2);
    }
}
