<?php

declare(strict_types=1);

namespace React\Promise\Tests\Unit;

use React\Promise\Deferred;

use function React\Promise\reduce;
use function React\Promise\reject;
use function React\Promise\resolve;

class FunctionReduceTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldReduceValuesWithoutInitialValue(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(6));

        reduce(
            [1, 2, 3],
            $this->plus(),
        )->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldReduceValuesWithInitialValue(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(7));

        reduce(
            [1, 2, 3],
            $this->plus(),
            1,
        )->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldReduceValuesWithInitialPromise(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(7));

        reduce(
            [1, 2, 3],
            $this->plus(),
            resolve(1),
        )->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldReducePromisedValuesWithoutInitialValue(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(6));

        reduce(
            [resolve(1), resolve(2), resolve(3)],
            $this->plus(),
        )->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldReducePromisedValuesWithInitialValue(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(7));

        reduce(
            [resolve(1), resolve(2), resolve(3)],
            $this->plus(),
            1,
        )->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldReducePromisedValuesWithInitialPromise(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(7));

        reduce(
            [resolve(1), resolve(2), resolve(3)],
            $this->plus(),
            resolve(1),
        )->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldReduceEmptyInputWithInitialValue(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        reduce(
            [],
            $this->plus(),
            1,
        )->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldReduceEmptyInputWithInitialPromise(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        reduce(
            [],
            $this->plus(),
            resolve(1),
        )->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldRejectWhenInputContainsRejection(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        reduce(
            [resolve(1), reject(2), resolve(3)],
            $this->plus(),
            resolve(1),
        )->then($this->expectCallableNever(), $mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldResolveWithNullWhenInputIsEmptyAndNoInitialValueOrPromiseProvided(): void
    {
        // Note: this is different from when.js's behavior!
        // In when.reduce(), this rejects with a TypeError exception (following
        // JavaScript's [].reduce behavior.
        // We're following PHP's array_reduce behavior and resolve with NULL.
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(null));

        reduce(
            [],
            $this->plus(),
        )->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldAllowSparseArrayInputWithoutInitialValue(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(3));

        reduce(
            [null, null, 1, null, 1, 1],
            $this->plus(),
        )->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldAllowSparseArrayInputWithInitialValue(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(4));

        reduce(
            [null, null, 1, null, 1, 1],
            $this->plus(),
            1,
        )->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldReduceInInputOrder(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo('123'));

        reduce(
            [1, 2, 3],
            $this->append(),
            '',
        )->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldAcceptAPromiseForAnArray(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo('123'));

        reduce(
            resolve([1, 2, 3]),
            $this->append(),
            '',
        )->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldResolveToInitialValueWhenInputPromiseDoesNotResolveToAnArray(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        reduce(
            resolve(1),
            $this->plus(),
            1,
        )->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldProvideCorrectBasisValue(): void
    {
        $insertIntoArray = static function ($arr, $val, $i) {
            $arr[$i] = $val;

            return $arr;
        };

        $d1 = new Deferred();
        $d2 = new Deferred();
        $d3 = new Deferred();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([1, 2, 3]));

        reduce(
            [$d1->promise(), $d2->promise(), $d3->promise()],
            $insertIntoArray,
            [],
        )->then($mock);

        $d3->resolve(3);
        $d1->resolve(1);
        $d2->resolve(2);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldRejectWhenInputPromiseRejects(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(null));

        reduce(
            reject(),
            $this->plus(),
            1,
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

        reduce(
            $mock,
            $this->plus(),
            1,
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

        reduce(
            [$mock1, $mock2],
            $this->plus(),
            1,
        )->cancel();
    }

    protected function plus()
    {
        return static fn($sum, $val) => $sum + $val;
    }

    protected function append()
    {
        return static fn($sum, $val) => $sum . $val;
    }
}
