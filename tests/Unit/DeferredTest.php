<?php

declare(strict_types=1);

namespace React\Promise\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use React\Promise\Deferred;
use React\Promise\Tests\Unit\PromiseAdapter\CallbackPromiseAdapter;

/**
 * @template T
 */
class DeferredTest extends TestCase
{
    use PromiseTest\FullTestTrait;

    /**
     * @return CallbackPromiseAdapter<T>
     */
    public function getPromiseTestAdapter(?callable $canceller = null): CallbackPromiseAdapter
    {
        $d = new Deferred($canceller);

        return new CallbackPromiseAdapter([
            'promise' => [$d, 'promise'],
            'resolve' => [$d, 'resolve'],
            'reject'  => [$d, 'reject'],
            'settle'  => [$d, 'resolve'],
        ]);
    }

    #[Test]
    public function shouldRejectWithoutCreatingGarbageCyclesIfCancellerRejectsWithException(): void
    {
        \gc_collect_cycles();
        $deferred = new Deferred(static function ($resolve, $reject): void {
            $reject(new \Exception('foo'));
        });
        $deferred->promise()->cancel();
        unset($deferred);

        $this->assertSame(0, \gc_collect_cycles());
    }

    #[Test]
    public function shouldRejectWithoutCreatingGarbageCyclesIfParentCancellerRejectsWithException(): void
    {
        \gc_collect_cycles();
        \gc_collect_cycles(); // clear twice to avoid leftovers in PHP 7.4 with ext-xdebug and code coverage turned on

        $deferred = new Deferred(static function ($resolve, $reject): void {
            $reject(new \Exception('foo'));
        });
        $deferred->promise()->then()->cancel();
        unset($deferred);

        $this->assertSame(0, \gc_collect_cycles());
    }

    #[Test]
    public function shouldRejectWithoutCreatingGarbageCyclesIfCancellerHoldsReferenceAndExplicitlyRejectWithException(): void
    {
        \gc_collect_cycles();
        \gc_collect_cycles(); // clear twice to avoid leftovers in PHP 7.4 with ext-xdebug and code coverage turned on

        /** @var Deferred<never> $deferred */
        $deferred = new Deferred(static function () use (&$deferred): void {
            \assert($deferred instanceof Deferred);
        });

        $deferred->promise()->then(null, $this->expectCallableOnce()); // avoid reporting unhandled rejection

        $deferred->reject(new \Exception('foo'));
        unset($deferred);

        $this->assertSame(0, \gc_collect_cycles());
    }
}
