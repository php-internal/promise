<?php

declare(strict_types=1);

namespace React\Promise\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use React\Promise\Deferred;
use React\Promise\Promise;

use function React\Promise\race;

class FunctionRaceTest extends TestCase
{
    #[Test]
    public function shouldReturnForeverPendingPromiseForEmptyInput(): void
    {
        race(
            [],
        )->then($this->expectCallableNever(), $this->expectCallableNever());
    }

    #[Test]
    public function shouldResolveValuesArray(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo(1));

        race(
            [1, 2, 3],
        )->then($mock);
    }

    #[Test]
    public function shouldResolvePromisesArray(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo(2));

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

    #[Test]
    public function shouldResolveSparseArrayInput(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo(null));

        race(
            [null, 1, null, 2, 3],
        )->then($mock);
    }

    #[Test]
    public function shouldResolveValuesGenerator(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo(1));

        $gen = (static function () {
            for ($i = 1; $i <= 3; ++$i) {
                yield $i;
            }
        })();

        race($gen)->then($mock);
    }

    #[Test]
    public function shouldResolveValuesInfiniteGenerator(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo(1));

        $gen = (static function () {
            for ($i = 1; ; ++$i) {
                yield $i;
            }
        })();

        race($gen)->then($mock);
    }

    #[Test]
    public function shouldRejectIfFirstSettledPromiseRejects(): void
    {
        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo($exception));

        $d1 = new Deferred();
        $d2 = new Deferred();
        $d3 = new Deferred();

        race(
            [$d1->promise(), $d2->promise(), $d3->promise()],
        )->then($this->expectCallableNever(), $mock);

        $d2->reject($exception);

        $d1->resolve(1);
        $d3->resolve(3);
    }

    #[Test]
    public function shouldCancelInputArrayPromises(): void
    {
        $promise1 = new Promise(static function (): void {}, $this->expectCallableOnce());
        $promise2 = new Promise(static function (): void {}, $this->expectCallableOnce());

        race([$promise1, $promise2])->cancel();
    }

    #[Test]
    public function shouldNotCancelOtherPendingInputArrayPromisesIfOnePromiseFulfills(): void
    {
        $deferred = new Deferred($this->expectCallableNever());
        $deferred->resolve(null);

        $promise2 = new Promise(static function (): void {}, $this->expectCallableNever());

        race([$deferred->promise(), $promise2])->cancel();
    }

    #[Test]
    public function shouldNotCancelOtherPendingInputArrayPromisesIfOnePromiseRejects(): void
    {
        $deferred = new Deferred($this->expectCallableNever());
        $deferred->reject(new \Exception());

        $promise2 = new Promise(static function (): void {}, $this->expectCallableNever());

        race([$deferred->promise(), $promise2])->cancel();
    }
}
