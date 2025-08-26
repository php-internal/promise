<?php

declare(strict_types=1);

namespace React\Promise\Tests\Unit\Fixture;

use React\Promise\PromiseInterface;
use React\Promise\RejectedPromise;

class SimpleRejectedTestPromise implements PromiseInterface
{
    public function then(?callable $onFulfilled = null, ?callable $onRejected = null, ?callable $onProgress = null)
    {
        try {
            if ($onRejected) {
                $onRejected('foo');
            }

            return new self();
        } catch (\Throwable|\Exception $exception) {
            return new RejectedPromise($exception);
        }
    }
}
