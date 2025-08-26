<?php

declare(strict_types=1);

namespace React\Promise\PromiseTest;

use React\Promise;

trait CancelTestTrait
{
    /**
     * @return \React\Promise\PromiseAdapter\PromiseAdapterInterface
     */
    abstract public function getPromiseTestAdapter(?callable $canceller = null);

    #[\PHPUnit\Framework\Attributes\Test]
    public function cancelShouldCallCancellerWithResolverArguments(): void
    {
        $args = null;
        $adapter = $this->getPromiseTestAdapter(static function ($resolve, $reject, $notify) use (&$args): void {
            $args = \func_get_args();
        });

        $adapter->promise()->cancel();

        $this->assertCount(3, $args);
        $this->assertTrue(\is_callable($args[0]));
        $this->assertTrue(\is_callable($args[1]));
        $this->assertTrue(\is_callable($args[2]));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function cancelShouldCallCancellerWithoutArgumentsIfNotAccessed(): void
    {
        $args = null;
        $adapter = $this->getPromiseTestAdapter(static function () use (&$args): void {
            $args = \func_num_args();
        });

        $adapter->promise()->cancel();

        $this->assertSame(0, $args);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function cancelShouldFulfillPromiseIfCancellerFulfills(): void
    {
        $adapter = $this->getPromiseTestAdapter(static function ($resolve): void {
            $resolve(1);
        });

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then($mock, $this->expectCallableNever());

        $adapter->promise()->cancel();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function cancelShouldRejectPromiseIfCancellerRejects(): void
    {
        $adapter = $this->getPromiseTestAdapter(static function ($resolve, $reject): void {
            $reject(1);
        });

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then($this->expectCallableNever(), $mock);

        $adapter->promise()->cancel();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function cancelShouldRejectPromiseWithExceptionIfCancellerThrows(): void
    {
        $e = new \Exception();

        $adapter = $this->getPromiseTestAdapter(static function () use ($e): void {
            throw $e;
        });

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($e));

        $adapter->promise()
            ->then($this->expectCallableNever(), $mock);

        $adapter->promise()->cancel();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function cancelShouldProgressPromiseIfCancellerNotifies(): void
    {
        $adapter = $this->getPromiseTestAdapter(static function ($resolve, $reject, $progress): void {
            $progress(1);
        });

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then($this->expectCallableNever(), $this->expectCallableNever(), $mock);

        $adapter->promise()->cancel();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function cancelShouldCallCancellerOnlyOnceIfCancellerResolves(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->returnCallback(static function ($resolve): void {
                $resolve();
            }));

        $adapter = $this->getPromiseTestAdapter($mock);

        $adapter->promise()->cancel();
        $adapter->promise()->cancel();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function cancelShouldHaveNoEffectIfCancellerDoesNothing(): void
    {
        $adapter = $this->getPromiseTestAdapter(static function (): void {});

        $adapter->promise()
            ->then($this->expectCallableNever(), $this->expectCallableNever());

        $adapter->promise()->cancel();
        $adapter->promise()->cancel();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function cancelShouldCallCancellerFromDeepNestedPromiseChain(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke');

        $adapter = $this->getPromiseTestAdapter($mock);

        $promise = $adapter->promise()
            ->then(static fn() => new Promise\Promise(static function (): void {}))
            ->then(static function () {
                $d = new Promise\Deferred();

                return $d->promise();
            })
            ->then(static fn() => new Promise\Promise(static function (): void {}));

        $promise->cancel();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function cancelCalledOnChildrenSouldOnlyCancelWhenAllChildrenCancelled(): void
    {
        $adapter = $this->getPromiseTestAdapter($this->expectCallableNever());

        $child1 = $adapter->promise()
            ->then()
            ->then();

        $adapter->promise()
            ->then();

        $child1->cancel();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function cancelShouldTriggerCancellerWhenAllChildrenCancel(): void
    {
        $adapter = $this->getPromiseTestAdapter($this->expectCallableOnce());

        $child1 = $adapter->promise()
            ->then()
            ->then();

        $child2 = $adapter->promise()
            ->then();

        $child1->cancel();
        $child2->cancel();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function cancelShouldNotTriggerCancellerWhenCancellingOneChildrenMultipleTimes(): void
    {
        $adapter = $this->getPromiseTestAdapter($this->expectCallableNever());

        $child1 = $adapter->promise()
            ->then()
            ->then();

        $child2 = $adapter->promise()
            ->then();

        $child1->cancel();
        $child1->cancel();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function cancelShouldTriggerCancellerOnlyOnceWhenCancellingMultipleTimes(): void
    {
        $adapter = $this->getPromiseTestAdapter($this->expectCallableOnce());

        $adapter->promise()->cancel();
        $adapter->promise()->cancel();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function cancelShouldAlwaysTriggerCancellerWhenCalledOnRootPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter($this->expectCallableOnce());

        $adapter->promise()
            ->then()
            ->then();

        $adapter->promise()
            ->then();

        $adapter->promise()->cancel();
    }
}
