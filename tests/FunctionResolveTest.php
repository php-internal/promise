<?php

declare(strict_types=1);

namespace React\Promise;

class FunctionResolveTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldResolveAnImmediateValue(): void
    {
        $expected = 123;

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($expected));

        resolve($expected)
            ->then(
                $mock,
                $this->expectCallableNever(),
            );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldResolveAFulfilledPromise(): void
    {
        $expected = 123;

        $resolved = new FulfilledPromise($expected);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($expected));

        resolve($resolved)
            ->then(
                $mock,
                $this->expectCallableNever(),
            );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldResolveAThenable(): void
    {
        $thenable = new SimpleFulfilledTestThenable();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo('foo'));

        resolve($thenable)
            ->then(
                $mock,
                $this->expectCallableNever(),
            );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldResolveACancellableThenable(): void
    {
        $thenable = new SimpleTestCancellableThenable();

        $promise = resolve($thenable);
        $promise->cancel();

        $this->assertTrue($thenable->cancelCalled);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldRejectARejectedPromise(): void
    {
        $expected = 123;

        $resolved = new RejectedPromise($expected);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($expected));

        resolve($resolved)
            ->then(
                $this->expectCallableNever(),
                $mock,
            );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldSupportDeepNestingInPromiseChains(): void
    {
        $d = new Deferred();
        $d->resolve(false);

        $result = resolve(resolve($d->promise()->then(static function ($val) {
            $d = new Deferred();
            $d->resolve($val);

            $identity = (static fn($val) => $val);

            return resolve($d->promise()->then($identity))->then(
                static fn($val) => !$val,
            );
        })));

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(true));

        $result->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldSupportVeryDeepNestedPromises(): void
    {
        $deferreds = [];

        // @TODO Increase count once global-queue is merged
        for ($i = 0; $i < 10; $i++) {
            $deferreds[] = $d = new Deferred();
            $p = $d->promise();

            $last = $p;
            for ($j = 0; $j < 10; $j++) {
                $last = $last->then(static fn($result) => $result);
            }
        }

        $p = null;
        foreach ($deferreds as $d) {
            if ($p) {
                $d->resolve($p);
            }

            $p = $d->promise();
        }

        $deferreds[0]->resolve(true);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(true));

        $deferreds[0]->promise()->then($mock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function returnsExtendePromiseForSimplePromise(): void
    {
        $promise = $this
            ->getMockBuilder(\React\Promise\PromiseInterface::class)
            ->getMock();

        $this->assertInstanceOf(\React\Promise\ExtendedPromiseInterface::class, resolve($promise));
    }
}
