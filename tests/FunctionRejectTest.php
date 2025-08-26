<?php

declare(strict_types=1);

namespace React\Promise;

class FunctionRejectTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldRejectAnImmediateValue(): void
    {
        $expected = 123;

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($expected));

        reject($expected)
            ->then(
                $this->expectCallableNever(),
                $mock,
            );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shouldRejectAFulfilledPromise(): void
    {
        $expected = 123;

        $resolved = new FulfilledPromise($expected);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($expected));

        reject($resolved)
            ->then(
                $this->expectCallableNever(),
                $mock,
            );
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

        reject($resolved)
            ->then(
                $this->expectCallableNever(),
                $mock,
            );
    }
}
