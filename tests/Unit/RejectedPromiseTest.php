<?php

declare(strict_types=1);

namespace React\Promise\Tests\Unit;

use React\Promise\RejectedPromise;
use React\Promise\Tests\Unit;
use React\Promise\Tests\Unit\PromiseAdapter\CallbackPromiseAdapter;

class RejectedPromiseTest extends TestCase
{
    use Unit\PromiseTest\PromiseSettledTestTrait;
    use Unit\PromiseTest\PromiseRejectedTestTrait;

    public function getPromiseTestAdapter(?callable $canceller = null)
    {
        $promise = null;

        return new CallbackPromiseAdapter([
            'promise' => static function () use (&$promise) {
                if (!$promise) {
                    throw new \LogicException('RejectedPromise must be rejected before obtaining the promise');
                }

                return $promise;
            },
            'resolve' => static function (): void {
                throw new \LogicException('You cannot call resolve() for React\Promise\RejectedPromise');
            },
            'reject' => static function ($reason = null) use (&$promise): void {
                if (!$promise) {
                    $promise = new RejectedPromise($reason);
                }
            },
            'notify' => static function (): void {
                // no-op
            },
            'settle' => static function ($reason = null) use (&$promise): void {
                if (!$promise) {
                    $promise = new RejectedPromise($reason);
                }
            },
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldThrowExceptionIfConstructedWithAPromise()
    {
        $this->setExpectedException('\InvalidArgumentException');

        return new RejectedPromise(new RejectedPromise());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldNotLeaveGarbageCyclesWhenRemovingLastReferenceToRejectedPromiseWithAlwaysFollowers(): void
    {
        \gc_collect_cycles();
        $promise = new RejectedPromise(1);
        $promise->always(static function (): void {
            throw new \RuntimeException();
        });
        unset($promise);

        $this->assertSame(0, \gc_collect_cycles());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldNotLeaveGarbageCyclesWhenRemovingLastReferenceToRejectedPromiseWithThenFollowers(): void
    {
        \gc_collect_cycles();
        $promise = new RejectedPromise(1);
        $promise = $promise->then(null, static function (): void {
            throw new \RuntimeException();
        });
        unset($promise);

        $this->assertSame(0, \gc_collect_cycles());
    }
}
