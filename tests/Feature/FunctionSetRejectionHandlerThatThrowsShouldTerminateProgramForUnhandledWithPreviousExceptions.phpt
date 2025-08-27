--TEST--
The callback given to set_rejection_handler() should not throw an exception or the program should terminate for unhandled rejection with all previous exceptions
--INI--
# suppress legacy PHPUnit 7 warning for Xdebug 3
xdebug.default_enable=
--FILE--
<?php

use function React\Promise\reject;
use function React\Promise\set_rejection_handler;

require __DIR__ . '/../vendor/autoload.php';

set_rejection_handler(function (Throwable $e): void {
    throw new RuntimeException('foo', 42, new \OverflowException('bar', 1000, new \InvalidArgumentException()));
});

reject(new RuntimeException('foo'));

echo 'NEVER';

?>
--EXPECTF--
Fatal error: Uncaught InvalidArgumentException from unhandled promise rejection handler in %s:%d
Stack trace:
#0 %A{main}

Next OverflowException: bar in %s:%d
Stack trace:
#0 %A{main}

Next RuntimeException: foo in %s:%d
Stack trace:
#0 %A{main}
