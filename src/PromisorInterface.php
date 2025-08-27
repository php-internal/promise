<?php

declare(strict_types=1);

namespace React\Promise;

/**
 * @template T
 */
interface PromisorInterface
{
    /**
     * Returns the promise of the deferred.
     *
     * @return PromiseInterface<T>
     */
    public function promise();
}
