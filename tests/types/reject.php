<?php

use React\Promise\PromiseInterface;
use function PHPStan\Testing\assertType;
use function React\Promise\reject;
use function React\Promise\resolve;

assertType('React\Promise\PromiseInterface<never>', reject(new RuntimeException()));
assertType('React\Promise\PromiseInterface<never>', reject(new RuntimeException())->then(null, null));
// assertType('React\Promise\PromiseInterface<never>', reject(new RuntimeException())->then(function (): int {
//     return 42;
// }));
assertType('React\Promise\PromiseInterface<int>', reject(new RuntimeException())->then(null, fn(): int => 42));
assertType('React\Promise\PromiseInterface<int>', reject(new RuntimeException())->then(null, fn(): PromiseInterface => resolve(42)));
// assertType('React\Promise\PromiseInterface<int>', reject(new RuntimeException())->then(function (): bool {
//     return true;
// }, function (): int {
//     return 42;
// }));

assertType('React\Promise\PromiseInterface<int>', reject(new RuntimeException())->catch(fn(): int => 42));
assertType('React\Promise\PromiseInterface<int>', reject(new RuntimeException())->catch(fn(\UnexpectedValueException $e): int => 42));
assertType('React\Promise\PromiseInterface<int>', reject(new RuntimeException())->catch(fn(): PromiseInterface => resolve(42)));

assertType('React\Promise\PromiseInterface<never>', reject(new RuntimeException())->finally(function (): void { }));
assertType('React\Promise\PromiseInterface<never>', reject(new RuntimeException())->finally(function (): never {
    throw new \UnexpectedValueException();
}));
assertType('React\Promise\PromiseInterface<never>', reject(new RuntimeException())->finally(fn(): PromiseInterface => reject(new \UnexpectedValueException())));

assertType('React\Promise\PromiseInterface<int>', reject(new RuntimeException())->otherwise(fn(): int => 42));
assertType('React\Promise\PromiseInterface<int>', reject(new RuntimeException())->otherwise(fn(\UnexpectedValueException $e): int => 42));
assertType('React\Promise\PromiseInterface<int>', reject(new RuntimeException())->otherwise(fn(): PromiseInterface => resolve(42)));

assertType('React\Promise\PromiseInterface<never>', reject(new RuntimeException())->always(function (): void { }));
assertType('React\Promise\PromiseInterface<never>', reject(new RuntimeException())->always(function (): never {
    throw new \UnexpectedValueException();
}));
assertType('React\Promise\PromiseInterface<never>', reject(new RuntimeException())->always(fn(): PromiseInterface => reject(new \UnexpectedValueException())));
