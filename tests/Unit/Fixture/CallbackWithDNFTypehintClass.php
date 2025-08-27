<?php

namespace React\Promise\Unit\Fixture;

class CallbackWithDNFTypehintClass
{
    public static function testCallbackStatic((\RuntimeException&\Countable)|(\RuntimeException&\IteratorAggregate) $e): void {}

    public function testCallback((\RuntimeException&\Countable)|(\RuntimeException&\IteratorAggregate) $e): void {}

    public function __invoke((\RuntimeException&\Countable)|(\RuntimeException&\IteratorAggregate) $e): void {}
}
