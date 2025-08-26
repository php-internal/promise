<?php

declare(strict_types=1);

namespace React\Promise;

class SimpleFulfilledTestThenable
{
    public function then(?callable $onFulfilled = null, ?callable $onRejected = null, ?callable $onProgress = null)
    {
        try {
            if ($onFulfilled) {
                $onFulfilled('foo');
            }

            return new self();
        } catch (\Throwable|\Exception $exception) {
            return new RejectedPromise($exception);
        }
    }
}
