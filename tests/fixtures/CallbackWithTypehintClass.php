<?php

declare(strict_types=1);

namespace React\Promise;

class CallbackWithTypehintClass
{
    public static function testCallbackStatic(\InvalidArgumentException $e): void {}

    public function testCallback(\InvalidArgumentException $e): void {}

    public function __invoke(\InvalidArgumentException $e): void {}
}
