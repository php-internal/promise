<?php

declare(strict_types=1);

namespace React\Promise\Tests\Unit;

use React\Promise\FulfilledPromise;
use React\Promise\Tests\Unit;
use React\Promise\Tests\Unit\PromiseAdapter\CallbackPromiseAdapter;

class FulfilledPromiseTest extends TestCase
{
    use Unit\PromiseTest\PromiseSettledTestTrait;
    use Unit\PromiseTest\PromiseFulfilledTestTrait;

    public function getPromiseTestAdapter(?callable $canceller = null)
    {
        $promise = null;

        return new CallbackPromiseAdapter([
            'promise' => static function () use (&$promise) {
                if (!$promise) {
                    throw new \LogicException('FulfilledPromise must be resolved before obtaining the promise');
                }

                return $promise;
            },
            'resolve' => static function ($value = null) use (&$promise): void {
                if (!$promise) {
                    $promise = new FulfilledPromise($value);
                }
            },
            'reject' => static function (): void {
                throw new \LogicException('You cannot call reject() for React\Promise\FulfilledPromise');
            },
            'notify' => static function (): void {
                // no-op
            },
            'settle' => static function ($value = null) use (&$promise): void {
                if (!$promise) {
                    $promise = new FulfilledPromise($value);
                }
            },
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldThrowExceptionIfConstructedWithAPromise()
    {
        $this->setExpectedException('\InvalidArgumentException');

        return new FulfilledPromise(new FulfilledPromise());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldNotLeaveGarbageCyclesWhenRemovingLastReferenceToFulfilledPromiseWithAlwaysFollowers(): void
    {
        \gc_collect_cycles();
        \gc_collect_cycles(); // clear twice to avoid leftovers in PHP 7.4 with ext-xdebug and code coverage turned on

        $promise = new FulfilledPromise(1);
        $promise->always(static function (): void {
            throw new \RuntimeException();
        });
        unset($promise);

        $this->assertSame(0, \gc_collect_cycles());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldNotLeaveGarbageCyclesWhenRemovingLastReferenceToFulfilledPromiseWithThenFollowers(): void
    {
        \gc_collect_cycles();
        $promise = new FulfilledPromise(1);
        $promise = $promise->then(static function (): void {
            throw new \RuntimeException();
        });
        unset($promise);

        $this->assertSame(0, \gc_collect_cycles());
    }
}
