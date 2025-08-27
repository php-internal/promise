<?php

declare(strict_types=1);

namespace React\Promise\Tests\Unit\Internal;

use React\Promise\Internal\RejectedPromise;
use React\Promise\Tests\Unit\PromiseAdapter\CallbackPromiseAdapter;
use React\Promise\Tests\Unit\PromiseTest\PromiseRejectedTestTrait;
use React\Promise\Tests\Unit\PromiseTest\PromiseSettledTestTrait;
use React\Promise\Tests\Unit\TestCase;

class RejectedPromiseTest extends TestCase
{
    use PromiseSettledTestTrait;
    use PromiseRejectedTestTrait;

    /**
     * @return CallbackPromiseAdapter<never>
     */
    public function getPromiseTestAdapter(?callable $canceller = null): CallbackPromiseAdapter
    {
        /** @var ?RejectedPromise */
        $promise = null;

        return new CallbackPromiseAdapter([
            'promise' => static function () use (&$promise) {
                if (!$promise) {
                    throw new \LogicException('RejectedPromise must be rejected before obtaining the promise');
                }

                return $promise;
            },
            'resolve' => static function (): void {
                throw new \LogicException('You cannot call resolve() for React\Promise\RejectedPromise');
            },
            'reject' => static function (\Throwable $reason) use (&$promise): void {
                if (!$promise) {
                    $promise = new RejectedPromise($reason);
                }
            },
            'settle' => static function ($reason = '') use (&$promise): void {
                if (!$promise) {
                    if (!$reason instanceof \Exception) {
                        $reason = new \Exception((string) $reason);
                    }

                    $promise = new RejectedPromise($reason);
                }
            },
        ]);
    }
}
