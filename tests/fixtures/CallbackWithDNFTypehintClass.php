<?php

namespace React\Promise;

use Countable;
use RuntimeException;

class CallbackWithDNFTypehintClass
{
    public function __invoke((RuntimeException&Countable)|(RuntimeException&\IteratorAggregate) $e): void { }

    public function testCallback((RuntimeException&Countable)|(RuntimeException&\IteratorAggregate) $e): void { }

    public static function testCallbackStatic((RuntimeException&Countable)|(RuntimeException&\IteratorAggregate) $e): void { }
}
