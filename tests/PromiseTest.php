<?php

declare(strict_types=1);

namespace React\Promise;

use React\Promise\PromiseAdapter\CallbackPromiseAdapter;

class PromiseTest extends TestCase
{
    use PromiseTest\FullTestTrait;

    public function getPromiseTestAdapter(?callable $canceller = null)
    {
        $resolveCallback = $rejectCallback = $progressCallback = null;

        $promise = new Promise(static function ($resolve, $reject, $progress) use (&$resolveCallback, &$rejectCallback, &$progressCallback): void {
            $resolveCallback  = $resolve;
            $rejectCallback   = $reject;
            $progressCallback = $progress;
        }, $canceller);

        return new CallbackPromiseAdapter([
            'promise' => static function () use ($promise) {
                return $promise;
            },
            'resolve' => $resolveCallback,
            'reject'  => $rejectCallback,
            'notify'  => $progressCallback,
            'settle'  => $resolveCallback,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldRejectIfResolverThrowsException(): void
    {
        $exception = new \Exception('foo');

        $promise = new Promise(static function () use ($exception): void {
            throw $exception;
        });

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $promise
            ->then($this->expectCallableNever(), $mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldResolveWithoutCreatingGarbageCyclesIfResolverResolvesWithException(): void
    {
        \gc_collect_cycles();
        \gc_collect_cycles(); // clear twice to avoid leftovers in PHP 7.4 with ext-xdebug and code coverage turned on

        $promise = new Promise(static function ($resolve): void {
            $resolve(new \Exception('foo'));
        });
        unset($promise);

        $this->assertSame(0, \gc_collect_cycles());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldRejectWithoutCreatingGarbageCyclesIfResolverThrowsExceptionWithoutResolver(): void
    {
        \gc_collect_cycles();
        $promise = new Promise(static function (): void {
            throw new \Exception('foo');
        });
        unset($promise);

        $this->assertSame(0, \gc_collect_cycles());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldRejectWithoutCreatingGarbageCyclesIfResolverRejectsWithException(): void
    {
        \gc_collect_cycles();
        $promise = new Promise(static function ($resolve, $reject): void {
            $reject(new \Exception('foo'));
        });
        unset($promise);

        $this->assertSame(0, \gc_collect_cycles());
    }

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldRejectWithoutCreatingGarbageCyclesIfResolverThrowsException(): void
    {
        \gc_collect_cycles();
        $promise = new Promise(static function ($resolve, $reject): void {
            throw new \Exception('foo');
        });
        unset($promise);

        $this->assertSame(0, \gc_collect_cycles());
    }

    /**
     * Test that checks number of garbage cycles after throwing from a canceller
     * that explicitly uses a reference to the promise. This is rather synthetic,
     * actual use cases often have implicit (hidden) references which ought not
     * to be stored in the stack trace.
     *
     * Reassigned arguments only show up in the stack trace in PHP 7, so we can't
     * avoid this on legacy PHP. As an alternative, consider explicitly unsetting
     * any references before throwing.
     */
    #[\PHPUnit\Framework\Attributes\RequiresPhp('7')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldRejectWithoutCreatingGarbageCyclesIfCancellerWithReferenceThrowsException(): void
    {
        \gc_collect_cycles();
        $promise = new Promise(static function (): void {}, static function () use (&$promise): void {
            throw new \Exception('foo');
        });
        $promise->cancel();
        unset($promise);

        $this->assertSame(0, \gc_collect_cycles());
    }

    /**
     * @see self::shouldRejectWithoutCreatingGarbageCyclesIfCancellerWithReferenceThrowsException
     */
    #[\PHPUnit\Framework\Attributes\RequiresPhp('7')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldRejectWithoutCreatingGarbageCyclesIfResolverWithReferenceThrowsException(): void
    {
        \gc_collect_cycles();
        $promise = new Promise(static function () use (&$promise): void {
            throw new \Exception('foo');
        });
        unset($promise);

        $this->assertSame(0, \gc_collect_cycles());
    }

    /**
     * @see self::shouldRejectWithoutCreatingGarbageCyclesIfCancellerWithReferenceThrowsException
     */
    #[\PHPUnit\Framework\Attributes\RequiresPhp('7')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldRejectWithoutCreatingGarbageCyclesIfCancellerHoldsReferenceAndResolverThrowsException(): void
    {
        \gc_collect_cycles();
        $promise = new Promise(static function (): void {
            throw new \Exception('foo');
        }, static function () use (&$promise): void {});
        unset($promise);

        $this->assertSame(0, \gc_collect_cycles());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldIgnoreNotifyAfterReject(): void
    {
        $promise = new Promise(static function (): void {}, static function ($resolve, $reject, $notify): void {
            $reject(new \Exception('foo'));
            $notify(42);
        });

        $promise->then(null, null, $this->expectCallableNever());
        $promise->cancel();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldNotLeaveGarbageCyclesWhenRemovingLastReferenceToPendingPromise(): void
    {
        \gc_collect_cycles();
        $promise = new Promise(static function (): void {});
        unset($promise);

        $this->assertSame(0, \gc_collect_cycles());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldNotLeaveGarbageCyclesWhenRemovingLastReferenceToPendingPromiseWithThenFollowers(): void
    {
        \gc_collect_cycles();
        $promise = new Promise(static function (): void {});
        $promise->then()->then()->then();
        unset($promise);

        $this->assertSame(0, \gc_collect_cycles());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldNotLeaveGarbageCyclesWhenRemovingLastReferenceToPendingPromiseWithDoneFollowers(): void
    {
        \gc_collect_cycles();
        $promise = new Promise(static function (): void {});
        $promise->done();
        unset($promise);

        $this->assertSame(0, \gc_collect_cycles());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldNotLeaveGarbageCyclesWhenRemovingLastReferenceToPendingPromiseWithOtherwiseFollowers(): void
    {
        \gc_collect_cycles();
        $promise = new Promise(static function (): void {});
        $promise->otherwise(static function (): void {});
        unset($promise);

        $this->assertSame(0, \gc_collect_cycles());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldNotLeaveGarbageCyclesWhenRemovingLastReferenceToPendingPromiseWithAlwaysFollowers(): void
    {
        \gc_collect_cycles();
        $promise = new Promise(static function (): void {});
        $promise->always(static function (): void {});
        unset($promise);

        $this->assertSame(0, \gc_collect_cycles());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldNotLeaveGarbageCyclesWhenRemovingLastReferenceToPendingPromiseWithProgressFollowers(): void
    {
        \gc_collect_cycles();
        $promise = new Promise(static function (): void {});
        $promise->then(null, null, static function (): void {});
        unset($promise);

        $this->assertSame(0, \gc_collect_cycles());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldFulfillIfFullfilledWithSimplePromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo('foo'));

        $adapter->promise()
            ->then($mock);

        $adapter->resolve(new SimpleFulfilledTestPromise());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldRejectIfRejectedWithSimplePromise(): void
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo('foo'));

        $adapter->promise()
            ->then($this->expectCallableNever(), $mock);

        $adapter->resolve(new SimpleRejectedTestPromise());
    }
}
