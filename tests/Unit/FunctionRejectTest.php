<?php

declare(strict_types=1);

namespace React\Promise\Unit;

use function React\Promise\reject;

class FunctionRejectTest extends TestCase
{
    /**
     * @test
     */
    public function shouldRejectAnException(): void
    {
        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo($exception));

        reject($exception)
            ->then($this->expectCallableNever(), $mock);
    }
}
