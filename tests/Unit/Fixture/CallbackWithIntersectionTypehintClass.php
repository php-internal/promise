<?php

declare(strict_types=1);

namespace React\Promise\Unit\Fixture;

class CallbackWithIntersectionTypehintClass
{
    public static function testCallbackStatic(\RuntimeException&\Countable $e): void {}

    public function testCallback(\RuntimeException&\Countable $e): void {}

    public function __invoke(\RuntimeException&\Countable $e): void {}
}
