<?php

declare(strict_types=1);

namespace React\Promise;

class CancellationQueueTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function acceptsSimpleCancellableThenable(): void
    {
        $p = new SimpleTestCancellableThenable();

        $cancellationQueue = new CancellationQueue();
        $cancellationQueue->enqueue($p);

        $cancellationQueue();

        $this->assertTrue($p->cancelCalled);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function ignoresSimpleCancellable(): void
    {
        $p = new SimpleTestCancellable();

        $cancellationQueue = new CancellationQueue();
        $cancellationQueue->enqueue($p);

        $cancellationQueue();

        $this->assertFalse($p->cancelCalled);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function callsCancelOnPromisesEnqueuedBeforeStart(): void
    {
        $d1 = $this->getCancellableDeferred();
        $d2 = $this->getCancellableDeferred();

        $cancellationQueue = new CancellationQueue();
        $cancellationQueue->enqueue($d1->promise());
        $cancellationQueue->enqueue($d2->promise());

        $cancellationQueue();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function callsCancelOnPromisesEnqueuedAfterStart(): void
    {
        $d1 = $this->getCancellableDeferred();
        $d2 = $this->getCancellableDeferred();

        $cancellationQueue = new CancellationQueue();

        $cancellationQueue();

        $cancellationQueue->enqueue($d2->promise());
        $cancellationQueue->enqueue($d1->promise());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function doesNotCallCancelTwiceWhenStartedTwice(): void
    {
        $d = $this->getCancellableDeferred();

        $cancellationQueue = new CancellationQueue();
        $cancellationQueue->enqueue($d->promise());

        $cancellationQueue();
        $cancellationQueue();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function rethrowsExceptionsThrownFromCancel(): void
    {
        $this->setExpectedException('\Exception', 'test');

        $mock = $this
            ->getMockBuilder('React\Promise\CancellablePromiseInterface')
            ->getMock();
        $mock
            ->expects($this->once())
            ->method('cancel')
            ->will($this->throwException(new \Exception('test')));

        $cancellationQueue = new CancellationQueue();
        $cancellationQueue->enqueue($mock);

        $cancellationQueue();
    }

    private function getCancellableDeferred()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke');

        return new Deferred($mock);
    }
}
