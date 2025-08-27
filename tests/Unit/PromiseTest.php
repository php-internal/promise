<?php

declare(strict_types=1);

namespace React\Promise\Unit;

use React\Promise\Promise;
use React\Promise\Unit\PromiseAdapter\CallbackPromiseAdapter;

/**
 * @template T
 */
class PromiseTest extends TestCase
{
    use PromiseTest\FullTestTrait;

    /**
     * @return CallbackPromiseAdapter<T>
     */
    public function getPromiseTestAdapter(?callable $canceller = null): CallbackPromiseAdapter
    {
        $resolveCallback = $rejectCallback = null;

        $promise = new Promise(static function (callable $resolve, callable $reject) use (&$resolveCallback, &$rejectCallback): void {
            $resolveCallback = $resolve;
            $rejectCallback  = $reject;
        }, $canceller);

        \assert(\is_callable($resolveCallback));
        \assert(\is_callable($rejectCallback));

        return new CallbackPromiseAdapter([
            'promise' => static fn() => $promise,
            'resolve' => $resolveCallback,
            'reject'  => $rejectCallback,
            'settle'  => $resolveCallback,
        ]);
    }

    /**
     * @test
     */
    public function shouldRejectIfResolverThrowsException(): void
    {
        $exception = new \Exception('foo');

        $promise = new Promise(static function () use ($exception): void {
            throw $exception;
        });

        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo($exception));

        $promise
            ->then($this->expectCallableNever(), $mock);
    }

    /**
     * @test
     */
    public function shouldResolveWithoutCreatingGarbageCyclesIfResolverResolvesWithException(): void
    {
        \gc_collect_cycles();
        $promise = new Promise(static function ($resolve): void {
            $resolve(new \Exception('foo'));
        });
        unset($promise);

        $this->assertSame(0, \gc_collect_cycles());
    }

    /**
     * @test
     */
    public function shouldRejectWithoutCreatingGarbageCyclesIfResolverThrowsExceptionWithoutResolver(): void
    {
        \gc_collect_cycles();
        $promise = new Promise(static function (): void {
            throw new \Exception('foo');
        });

        $promise->then(null, $this->expectCallableOnce()); // avoid reporting unhandled rejection

        unset($promise);

        $this->assertSame(0, \gc_collect_cycles());
    }

    /**
     * @test
     */
    public function shouldRejectWithoutCreatingGarbageCyclesIfResolverRejectsWithException(): void
    {
        \gc_collect_cycles();
        $promise = new Promise(static function ($resolve, $reject): void {
            $reject(new \Exception('foo'));
        });

        $promise->then(null, $this->expectCallableOnce()); // avoid reporting unhandled rejection

        unset($promise);

        $this->assertSame(0, \gc_collect_cycles());
    }

    /**
     * @test
     */
    public function shouldRejectWithoutCreatingGarbageCyclesIfCancellerRejectsWithException(): void
    {
        \gc_collect_cycles();
        $promise = new Promise(static function ($resolve, $reject): void {}, static function ($resolve, $reject): void {
            $reject(new \Exception('foo'));
        });
        $promise->cancel();
        unset($promise);

        $this->assertSame(0, \gc_collect_cycles());
    }

    /**
     * @test
     */
    public function shouldRejectWithoutCreatingGarbageCyclesIfParentCancellerRejectsWithException(): void
    {
        \gc_collect_cycles();
        $promise = new Promise(static function ($resolve, $reject): void {}, static function ($resolve, $reject): void {
            $reject(new \Exception('foo'));
        });
        $promise->then()->then()->then()->cancel();
        unset($promise);

        $this->assertSame(0, \gc_collect_cycles());
    }

    /**
     * @test
     */
    public function shouldRejectWithoutCreatingGarbageCyclesIfResolverThrowsException(): void
    {
        \gc_collect_cycles();
        $promise = new Promise(static function ($resolve, $reject): void {
            throw new \Exception('foo');
        });

        $promise->then(null, $this->expectCallableOnce()); // avoid reporting unhandled rejection

        unset($promise);

        $this->assertSame(0, \gc_collect_cycles());
    }

    /**
     * @test
     */
    public function shouldNotLeaveGarbageCyclesWhenRemovingLastReferenceToPendingPromise(): void
    {
        \gc_collect_cycles();
        $promise = new Promise(static function (): void {});
        unset($promise);

        $this->assertSame(0, \gc_collect_cycles());
    }

    /**
     * @test
     */
    public function shouldNotLeaveGarbageCyclesWhenRemovingLastReferenceToPendingPromiseWithThenFollowers(): void
    {
        \gc_collect_cycles();
        $promise = new Promise(static function (): void {});
        $promise->then()->then()->then();
        unset($promise);

        $this->assertSame(0, \gc_collect_cycles());
    }

    /**
     * @test
     */
    public function shouldNotLeaveGarbageCyclesWhenRemovingLastReferenceToPendingPromiseWithCatchFollowers(): void
    {
        \gc_collect_cycles();
        $promise = new Promise(static function (): void {});
        $promise->catch(static function (): void {});
        unset($promise);

        $this->assertSame(0, \gc_collect_cycles());
    }

    /**
     * @test
     */
    public function shouldNotLeaveGarbageCyclesWhenRemovingLastReferenceToPendingPromiseWithFinallyFollowers(): void
    {
        \gc_collect_cycles();
        $promise = new Promise(static function (): void {});
        $promise->finally(static function (): void {});
        unset($promise);

        $this->assertSame(0, \gc_collect_cycles());
    }

    /**
     * @test
     * @deprecated
     */
    public function shouldNotLeaveGarbageCyclesWhenRemovingLastReferenceToPendingPromiseWithOtherwiseFollowers(): void
    {
        \gc_collect_cycles();
        $promise = new Promise(static function (): void {});
        $promise->otherwise(static function (): void {});
        unset($promise);

        $this->assertSame(0, \gc_collect_cycles());
    }

    /**
     * @test
     * @deprecated
     */
    public function shouldNotLeaveGarbageCyclesWhenRemovingLastReferenceToPendingPromiseWithAlwaysFollowers(): void
    {
        \gc_collect_cycles();
        $promise = new Promise(static function (): void {});
        $promise->always(static function (): void {});
        unset($promise);

        $this->assertSame(0, \gc_collect_cycles());
    }

    /**
     * @test
     */
    public function shouldFulfillIfFullfilledWithSimplePromise(): void
    {
        \gc_collect_cycles();
        $promise = new Promise(static function (): void {
            throw new \Exception('foo');
        });

        $promise->then(null, $this->expectCallableOnce()); // avoid reporting unhandled rejection

        unset($promise);

        self::assertSame(0, \gc_collect_cycles());
    }
}
