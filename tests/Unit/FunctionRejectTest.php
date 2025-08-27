<?php

declare(strict_types=1);

namespace React\Promise\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;

use function React\Promise\reject;

class FunctionRejectTest extends TestCase
{
    #[Test]
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
