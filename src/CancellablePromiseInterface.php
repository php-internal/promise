<?php

declare(strict_types=1);

namespace React\Promise;

/**
 * @template T
 * @extends PromiseInterface<T>
 */
interface CancellablePromiseInterface extends PromiseInterface
{
    /**
     * The `cancel()` method notifies the creator of the promise that there is no
     * further interest in the results of the operation.
     *
     * Once a promise is settled (either fulfilled or rejected), calling `cancel()` on
     * a promise has no effect.
     *
     * @return void
     */
    public function cancel();
}
