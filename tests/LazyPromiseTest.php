<?php

declare(strict_types=1);

namespace React\Promise;

use React\Promise\PromiseAdapter\CallbackPromiseAdapter;

class LazyPromiseTest extends TestCase
{
    use PromiseTest\FullTestTrait;

    public function getPromiseTestAdapter(?callable $canceller = null)
    {
        $d = new Deferred($canceller);

        $factory = (static fn() => $d->promise());

        return new CallbackPromiseAdapter([
            'promise'  => static fn() => new LazyPromise($factory),
            'resolve' => $d->resolve(...),
            'reject'  => $d->reject(...),
            'notify'  => $d->progress(...),
            'settle'  => $d->resolve(...),
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldNotCallFactoryIfThenIsNotInvoked(): void
    {
        $factory = $this->createCallableMock();
        $factory
            ->expects($this->never())
            ->method('__invoke');

        new LazyPromise($factory);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldCallFactoryIfThenIsInvoked(): void
    {
        $factory = $this->createCallableMock();
        $factory
            ->expects($this->once())
            ->method('__invoke');

        $p = new LazyPromise($factory);
        $p->then();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldReturnPromiseFromFactory(): void
    {
        $factory = $this->createCallableMock();
        $factory
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->returnValue(new FulfilledPromise(1)));

        $onFulfilled = $this->createCallableMock();
        $onFulfilled
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $p = new LazyPromise($factory);

        $p->then($onFulfilled);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldReturnPromiseIfFactoryReturnsNull(): void
    {
        $factory = $this->createCallableMock();
        $factory
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->returnValue(null));

        $p = new LazyPromise($factory);
        $this->assertInstanceOf(\React\Promise\PromiseInterface::class, $p->then());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldReturnRejectedPromiseIfFactoryThrowsException(): void
    {
        $exception = new \Exception();

        $factory = $this->createCallableMock();
        $factory
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->throwException($exception));

        $onRejected = $this->createCallableMock();
        $onRejected
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $p = new LazyPromise($factory);

        $p->then($this->expectCallableNever(), $onRejected);
    }
}
