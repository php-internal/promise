<?php

declare(strict_types=1);

use React\Promise\PromiseInterface;

use function PHPStan\Testing\assertType;
use function React\Promise\reject;
use function React\Promise\resolve;

assertType('React\Promise\PromiseInterface<never>', reject(new RuntimeException()));
assertType('React\Promise\PromiseInterface<never>', reject(new RuntimeException())->then(null, null));
// assertType('React\Promise\PromiseInterface<never>', reject(new RuntimeException())->then(function (): int {
//     return 42;
// }));
assertType('React\Promise\PromiseInterface<int>', reject(new RuntimeException())->then(null, static fn(): int => 42));
assertType('React\Promise\PromiseInterface<int>', reject(new RuntimeException())->then(null, static fn(): PromiseInterface => resolve(42)));
// assertType('React\Promise\PromiseInterface<int>', reject(new RuntimeException())->then(function (): bool {
//     return true;
// }, function (): int {
//     return 42;
// }));

assertType('React\Promise\PromiseInterface<int>', reject(new RuntimeException())->catch(static fn(): int => 42));
assertType('React\Promise\PromiseInterface<int>', reject(new RuntimeException())->catch(static fn(\UnexpectedValueException $e): int => 42));
assertType('React\Promise\PromiseInterface<int>', reject(new RuntimeException())->catch(static fn(): PromiseInterface => resolve(42)));

assertType('React\Promise\PromiseInterface<never>', reject(new RuntimeException())->finally(static function (): void {}));
assertType('React\Promise\PromiseInterface<never>', reject(new RuntimeException())->finally(static function (): never {
    throw new \UnexpectedValueException();
}));
assertType('React\Promise\PromiseInterface<never>', reject(new RuntimeException())->finally(static fn(): PromiseInterface => reject(new \UnexpectedValueException())));

assertType('React\Promise\PromiseInterface<int>', reject(new RuntimeException())->otherwise(static fn(): int => 42));
assertType('React\Promise\PromiseInterface<int>', reject(new RuntimeException())->otherwise(static fn(\UnexpectedValueException $e): int => 42));
assertType('React\Promise\PromiseInterface<int>', reject(new RuntimeException())->otherwise(static fn(): PromiseInterface => resolve(42)));

assertType('React\Promise\PromiseInterface<never>', reject(new RuntimeException())->always(static function (): void {}));
assertType('React\Promise\PromiseInterface<never>', reject(new RuntimeException())->always(static function (): never {
    throw new \UnexpectedValueException();
}));
assertType('React\Promise\PromiseInterface<never>', reject(new RuntimeException())->always(static fn(): PromiseInterface => reject(new \UnexpectedValueException())));
