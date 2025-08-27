<?php

declare(strict_types=1);

namespace React\Promise;

class CallbackWithoutTypehintClass
{
    public static function testCallbackStatic(): void {}

    public function testCallback(): void {}

    public function __invoke(): void {}
}
