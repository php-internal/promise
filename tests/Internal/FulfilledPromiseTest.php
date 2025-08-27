<?php

declare(strict_types=1);

namespace React\Promise\Internal;

use React\Promise\PromiseAdapter\CallbackPromiseAdapter;
use React\Promise\PromiseTest\PromiseFulfilledTestTrait;
use React\Promise\PromiseTest\PromiseSettledTestTrait;
use React\Promise\TestCase;

/**
 * @template T
 */
class FulfilledPromiseTest extends TestCase
{
    use PromiseSettledTestTrait;
    use PromiseFulfilledTestTrait;

    /**
     * @return CallbackPromiseAdapter<T>
     */
    public function getPromiseTestAdapter(?callable $canceller = null): CallbackPromiseAdapter
    {
        /** @var ?FulfilledPromise<T> */
        $promise = null;

        return new CallbackPromiseAdapter([
            'promise' => static function () use (&$promise) {
                if (!$promise) {
                    throw new \LogicException('FulfilledPromise must be resolved before obtaining the promise');
                }

                return $promise;
            },
            'resolve' => static function ($value = null) use (&$promise): void {
                if (!$promise) {
                    $promise = new FulfilledPromise($value);
                }
            },
            'reject' => static function (): void {
                throw new \LogicException('You cannot call reject() for React\Promise\FulfilledPromise');
            },
            'settle' => static function ($value = null) use (&$promise): void {
                if (!$promise) {
                    $promise = new FulfilledPromise($value);
                }
            },
        ]);
    }

    /**
     * @test
     */
    public function shouldThrowExceptionIfConstructedWithAPromise(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new FulfilledPromise(new FulfilledPromise());
    }
}
