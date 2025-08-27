<?php

declare(strict_types=1);

namespace React\Promise\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use React\Promise\Deferred;

use function React\Promise\all;
use function React\Promise\reject;
use function React\Promise\resolve;

class FunctionAllTest extends TestCase
{
    #[Test]
    public function shouldResolveEmptyInput(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo([]));

        all([])
            ->then($mock);
    }

    #[Test]
    public function shouldResolveValuesArray(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo([1, 2, 3]));

        all([1, 2, 3])
            ->then($mock);
    }

    #[Test]
    public function shouldResolvePromisesArray(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo([1, 2, 3]));

        all([resolve(1), resolve(2), resolve(3)])
            ->then($mock);
    }

    #[Test]
    public function shouldResolveSparseArrayInput(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo([null, 1, null, 1, 1]));

        all([null, 1, null, 1, 1])
            ->then($mock);
    }

    #[Test]
    public function shouldResolveValuesGenerator(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo([1, 2, 3]));

        $gen = (static function () {
            for ($i = 1; $i <= 3; ++$i) {
                yield $i;
            }
        })();

        all($gen)->then($mock);
    }

    #[Test]
    public function shouldResolveValuesGeneratorEmpty(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo([]));

        $gen = (static function () {
            if (false) { // @phpstan-ignore-line
                yield;
            }
        })();

        all($gen)->then($mock);
    }

    #[Test]
    public function shouldRejectIfAnyInputPromiseRejects(): void
    {
        $exception2 = new \Exception();
        $exception3 = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo($exception2));

        all([resolve(1), reject($exception2), reject($exception3)])
            ->then($this->expectCallableNever(), $mock);
    }

    #[Test]
    public function shouldRejectInfiteGeneratorOrRejectedPromises(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(new \RuntimeException('Iteration 1'));

        $gen = (static function () {
            for ($i = 1; ; ++$i) {
                yield reject(new \RuntimeException('Iteration ' . $i));
            }
        })();

        all($gen)->then(null, $mock);
    }

    #[Test]
    public function shouldPreserveTheOrderOfArrayWhenResolvingAsyncPromises(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo([1, 2, 3]));

        $deferred = new Deferred();

        all([resolve(1), $deferred->promise(), resolve(3)])
            ->then($mock);

        $deferred->resolve(2);
    }
}
