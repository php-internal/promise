<?php

declare(strict_types=1);

namespace React\Promise\Unit\Fixture;

class CallbackWithUnionTypehintClass
{
    public static function testCallbackStatic(\RuntimeException|\InvalidArgumentException $e): void {}

    public function testCallback(\RuntimeException|\InvalidArgumentException $e): void {}

    public function __invoke(\RuntimeException|\InvalidArgumentException $e): void {}
}
