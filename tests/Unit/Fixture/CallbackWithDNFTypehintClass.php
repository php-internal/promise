<?php

namespace React\Promise\Tests\Unit\Fixture;

class CallbackWithDNFTypehintClass
{
    public static function testCallbackStatic((\RuntimeException&\Countable)|(\RuntimeException&\ArrayAccess) $e) {}

    public function testCallback((\RuntimeException&\Countable)|(\RuntimeException&\ArrayAccess) $e) {}

    public function __invoke((\RuntimeException&\Countable)|(\RuntimeException&\ArrayAccess) $e) {}
}
