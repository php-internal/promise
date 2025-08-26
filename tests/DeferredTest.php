<?php

declare(strict_types=1);

namespace React\Promise;

use React\Promise\PromiseAdapter\CallbackPromiseAdapter;

class DeferredTest extends TestCase
{
    use PromiseTest\FullTestTrait;

    public function getPromiseTestAdapter(?callable $canceller = null)
    {
        $d = new Deferred($canceller);

        return new CallbackPromiseAdapter([
            'promise' => [$d, 'promise'],
            'resolve' => [$d, 'resolve'],
            'reject'  => [$d, 'reject'],
            'notify'  => [$d, 'progress'],
            'settle'  => [$d, 'resolve'],
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function progressIsAnAliasForNotify(): void
    {
        $deferred = new Deferred();

        $sentinel = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($sentinel);

        $deferred->promise()
            ->then($this->expectCallableNever(), $this->expectCallableNever(), $mock);

        $deferred->progress($sentinel);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldRejectWithoutCreatingGarbageCyclesIfCancellerRejectsWithException(): void
    {
        \gc_collect_cycles();
        \gc_collect_cycles(); // clear twice to avoid leftovers in PHP 7.4 with ext-xdebug and code coverage turned on

        $deferred = new Deferred(static function ($resolve, $reject): void {
            $reject(new \Exception('foo'));
        });
        $deferred->promise()->cancel();
        unset($deferred);

        $this->assertSame(0, \gc_collect_cycles());
    }

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldRejectWithoutCreatingGarbageCyclesIfCancellerHoldsReferenceAndExplicitlyRejectWithException(): void
    {
        \gc_collect_cycles();
        $deferred = new Deferred(static function () use (&$deferred): void {});
        $deferred->reject(new \Exception('foo'));
        unset($deferred);

        $this->assertSame(0, \gc_collect_cycles());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldNotLeaveGarbageCyclesWhenRemovingLastReferenceToPendingDeferred(): void
    {
        \gc_collect_cycles();
        $deferred = new Deferred();
        $deferred->promise();
        unset($deferred);

        $this->assertSame(0, \gc_collect_cycles());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldNotLeaveGarbageCyclesWhenRemovingLastReferenceToPendingDeferredWithUnusedCanceller(): void
    {
        \gc_collect_cycles();
        $deferred = new Deferred(static function (): void {});
        $deferred->promise();
        unset($deferred);

        $this->assertSame(0, \gc_collect_cycles());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldNotLeaveGarbageCyclesWhenRemovingLastReferenceToPendingDeferredWithNoopCanceller(): void
    {
        \gc_collect_cycles();
        $deferred = new Deferred(static function (): void {});
        $deferred->promise()->cancel();
        unset($deferred);

        $this->assertSame(0, \gc_collect_cycles());
    }
}
