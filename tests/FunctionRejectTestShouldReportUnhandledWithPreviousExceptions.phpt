--TEST--
Calling reject() without any handlers should report unhandled rejection with all previous exceptions
--INI--
# suppress legacy PHPUnit 7 warning for Xdebug 3
xdebug.default_enable=
--FILE--
<?php

use function React\Promise\reject;

require __DIR__ . '/../vendor/autoload.php';

reject(new RuntimeException('foo', 42, new \OverflowException('bar', 1000, new \InvalidArgumentException())));

?>
--EXPECTF--
Unhandled promise rejection with InvalidArgumentException in %s:%d
Stack trace:
#0 %A{main}

Next OverflowException: bar in %s:%d
Stack trace:
#0 %A{main}

Next RuntimeException: foo in %s:%d
Stack trace:
#0 %A{main}
