<?php

declare(strict_types=1);

namespace React\Promise\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use React\Promise\Tests\Unit\Fixture\SimpleFulfilledTestThenable;
use React\Promise\Tests\Unit\Fixture\SimpleTestCancellableThenable;
use React\Promise\Deferred;
use React\Promise\Internal\FulfilledPromise;
use React\Promise\Internal\RejectedPromise;

use function React\Promise\resolve;

class FunctionResolveTest extends TestCase
{
    #[Test]
    public function shouldResolveAnImmediateValue(): void
    {
        $expected = 123;

        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo($expected));

        resolve($expected)
            ->then(
                $mock,
                $this->expectCallableNever(),
            );
    }

    #[Test]
    public function shouldResolveAFulfilledPromise(): void
    {
        $expected = 123;

        $resolved = new FulfilledPromise($expected);

        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo($expected));

        resolve($resolved)
            ->then(
                $mock,
                $this->expectCallableNever(),
            );
    }

    #[Test]
    public function shouldResolveAThenable(): void
    {
        $thenable = new SimpleFulfilledTestThenable();

        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo('foo'));

        resolve($thenable)
            ->then(
                $mock,
                $this->expectCallableNever(),
            );
    }

    #[Test]
    public function shouldResolveACancellableThenable(): void
    {
        $thenable = new SimpleTestCancellableThenable();

        $promise = resolve($thenable);
        $promise->cancel();

        self::assertTrue($thenable->cancelCalled);
    }

    #[Test]
    public function shouldRejectARejectedPromise(): void
    {
        $exception = new \Exception();

        $resolved = new RejectedPromise($exception);

        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo($exception));

        resolve($resolved)
            ->then(
                $this->expectCallableNever(),
                $mock,
            );
    }

    #[Test]
    public function shouldSupportDeepNestingInPromiseChains(): void
    {
        $d = new Deferred();
        $d->resolve(false);

        $result = resolve(resolve($d->promise()->then(static function ($val) {
            $d = new Deferred();
            $d->resolve($val);

            $identity = static fn($val) => $val;

            return resolve($d->promise()->then($identity))->then(
                static fn($val) => !$val,
            );
        })));

        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo(true));

        $result->then($mock);
    }

    #[Test]
    public function shouldSupportVeryDeepNestedPromises(): void
    {
        $deferreds = [];

        for ($i = 0; $i < 150; $i++) {
            $deferreds[] = $d = new Deferred();
            $p = $d->promise();

            $last = $p;
            for ($j = 0; $j < 150; $j++) {
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
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo(true));

        $deferreds[0]->promise()->then($mock);
    }
}
