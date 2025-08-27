<?php

declare(strict_types=1);

use React\Promise\PromiseInterface;

use function PHPStan\Testing\assertType;
use function React\Promise\reject;
use function React\Promise\resolve;

/**
 * @return int|string
 */
function stringOrInt()
{
    return \time() % 2 ? 'string' : \time();
};

/**
 * @return PromiseInterface<int|string>
 */
function stringOrIntPromise(): PromiseInterface
{
    return resolve(\time() % 2 ? 'string' : \time());
};

assertType('React\Promise\PromiseInterface<bool>', resolve(true));
assertType('React\Promise\PromiseInterface<int|string>', resolve(stringOrInt()));
assertType('React\Promise\PromiseInterface<int|string>', stringOrIntPromise());
assertType('React\Promise\PromiseInterface<bool>', resolve(resolve(true)));

assertType('React\Promise\PromiseInterface<bool>', resolve(true)->then(null, null));
assertType('React\Promise\PromiseInterface<bool>', resolve(true)->then(static fn(bool $bool): bool => $bool));
assertType('React\Promise\PromiseInterface<int>', resolve(true)->then(static fn(bool $value): int => 42));
assertType('React\Promise\PromiseInterface<int>', resolve(true)->then(static fn(bool $value): PromiseInterface => resolve(42)));
assertType('React\Promise\PromiseInterface<never>', resolve(true)->then(static function (bool $value): never {
    throw new \RuntimeException();
}));
assertType('React\Promise\PromiseInterface<bool|int>', resolve(true)->then(null, static fn(\Throwable $e): int => 42));

assertType('React\Promise\PromiseInterface<void>', resolve(true)->then(static function (bool $bool): void {}));
assertType('React\Promise\PromiseInterface<void>', resolve(false)->then(static function (bool $bool): void {})->then(static function (null $value): void {}));

$value = null;
assertType('React\Promise\PromiseInterface<void>', resolve(true)->then(static function (bool $v) use (&$value): void {
    $value = $v;
}));
assertType('bool|null', $value);

assertType('React\Promise\PromiseInterface<bool>', resolve(true)->catch(static function (\Throwable $e): never {
    throw $e;
}));
assertType('React\Promise\PromiseInterface<bool|int>', resolve(true)->catch(static fn(\Throwable $e): int => 42));
assertType('React\Promise\PromiseInterface<bool|int>', resolve(true)->catch(static fn(\Throwable $e): PromiseInterface => resolve(42)));

assertType('React\Promise\PromiseInterface<bool>', resolve(true)->finally(static function (): void {}));
// assertType('React\Promise\PromiseInterface<never>', resolve(true)->finally(function (): never {
//     throw new \RuntimeException();
// }));
// assertType('React\Promise\PromiseInterface<never>', resolve(true)->finally(function (): PromiseInterface {
//     return reject(new \RuntimeException());
// }));

assertType('React\Promise\PromiseInterface<bool>', resolve(true)->otherwise(static function (\Throwable $e): never {
    throw $e;
}));
assertType('React\Promise\PromiseInterface<bool|int>', resolve(true)->otherwise(static fn(\Throwable $e): int => 42));
assertType('React\Promise\PromiseInterface<bool|int>', resolve(true)->otherwise(static fn(\Throwable $e): PromiseInterface => resolve(42)));

assertType('React\Promise\PromiseInterface<bool>', resolve(true)->always(static function (): void {}));
// assertType('React\Promise\PromiseInterface<never>', resolve(true)->always(function (): never {
//     throw new \RuntimeException();
// }));
// assertType('React\Promise\PromiseInterface<never>', resolve(true)->always(function (): PromiseInterface {
//     return reject(new \RuntimeException());
// }));
