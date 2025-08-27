<?php

declare(strict_types=1);

namespace React\Promise\Tests\Unit\Internal;

use PHPUnit\Framework\Attributes\Test;
use React\Promise\Deferred;
use React\Promise\Internal\CancellationQueue;
use React\Promise\Tests\Unit\Fixture\SimpleTestCancellable;
use React\Promise\Tests\Unit\Fixture\SimpleTestCancellableThenable;
use React\Promise\Tests\Unit\TestCase;

class CancellationQueueTest extends TestCase
{
    #[Test]
    public function acceptsSimpleCancellableThenable(): void
    {
        $p = new SimpleTestCancellableThenable();

        $cancellationQueue = new CancellationQueue();
        $cancellationQueue->enqueue($p);

        $cancellationQueue();

        self::assertTrue($p->cancelCalled);
    }

    #[Test]
    public function ignoresSimpleCancellable(): void
    {
        $p = new SimpleTestCancellable();

        $cancellationQueue = new CancellationQueue();
        $cancellationQueue->enqueue($p);

        $cancellationQueue();

        self::assertFalse($p->cancelCalled);
    }

    #[Test]
    public function callsCancelOnPromisesEnqueuedBeforeStart(): void
    {
        $d1 = $this->getCancellableDeferred();
        $d2 = $this->getCancellableDeferred();

        $cancellationQueue = new CancellationQueue();
        $cancellationQueue->enqueue($d1->promise());
        $cancellationQueue->enqueue($d2->promise());

        $cancellationQueue();
    }

    #[Test]
    public function callsCancelOnPromisesEnqueuedAfterStart(): void
    {
        $d1 = $this->getCancellableDeferred();
        $d2 = $this->getCancellableDeferred();

        $cancellationQueue = new CancellationQueue();

        $cancellationQueue();

        $cancellationQueue->enqueue($d2->promise());
        $cancellationQueue->enqueue($d1->promise());
    }

    #[Test]
    public function doesNotCallCancelTwiceWhenStartedTwice(): void
    {
        $d = $this->getCancellableDeferred();

        $cancellationQueue = new CancellationQueue();
        $cancellationQueue->enqueue($d->promise());

        $cancellationQueue();
        $cancellationQueue();
    }

    #[Test]
    public function rethrowsExceptionsThrownFromCancel(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('test');
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->will(self::throwException(new \Exception('test')));

        $promise = new SimpleTestCancellableThenable($mock);

        $cancellationQueue = new CancellationQueue();
        $cancellationQueue->enqueue($promise);

        $cancellationQueue();
    }

    /**
     * @return Deferred<never>
     */
    private function getCancellableDeferred(): Deferred
    {
        /** @var Deferred<never> */
        return new Deferred($this->expectCallableOnce());
    }
}
