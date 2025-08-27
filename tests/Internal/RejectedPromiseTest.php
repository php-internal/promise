<?php

namespace React\Promise\Internal;

use Exception;
use LogicException;
use React\Promise\PromiseAdapter\CallbackPromiseAdapter;
use React\Promise\PromiseTest\PromiseRejectedTestTrait;
use React\Promise\PromiseTest\PromiseSettledTestTrait;
use React\Promise\TestCase;

class RejectedPromiseTest extends TestCase
{
    use PromiseSettledTestTrait,
        PromiseRejectedTestTrait;

    /**
     * @return CallbackPromiseAdapter<never>
     */
    public function getPromiseTestAdapter(?callable $canceller = null): CallbackPromiseAdapter
    {
        /** @var ?RejectedPromise */
        $promise = null;

        return new CallbackPromiseAdapter([
            'promise' => function () use (&$promise) {
                if (!$promise) {
                    throw new LogicException('RejectedPromise must be rejected before obtaining the promise');
                }

                return $promise;
            },
            'resolve' => function (): void {
                throw new LogicException('You cannot call resolve() for React\Promise\RejectedPromise');
            },
            'reject' => function (\Throwable $reason) use (&$promise): void {
                if (!$promise) {
                    $promise = new RejectedPromise($reason);
                }
            },
            'settle' => function ($reason = '') use (&$promise): void {
                if (!$promise) {
                    if (!$reason instanceof Exception) {
                        $reason = new Exception((string) $reason);
                    }

                    $promise = new RejectedPromise($reason);
                }
            },
        ]);
    }
}
