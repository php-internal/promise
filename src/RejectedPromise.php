<?php

declare(strict_types=1);

namespace React\Promise;

/**
 * @deprecated 2.8.0 External usage of RejectedPromise is deprecated, use `reject()` instead.
 */
class RejectedPromise implements ExtendedPromiseInterface, CancellablePromiseInterface
{
    private $reason;

    public function __construct($reason = null)
    {
        if ($reason instanceof PromiseInterface) {
            throw new \InvalidArgumentException('You cannot create React\Promise\RejectedPromise with a promise. Use React\Promise\reject($promiseOrValue) instead.');
        }

        $this->reason = $reason;
    }

    public function then(?callable $onFulfilled = null, ?callable $onRejected = null, ?callable $onProgress = null)
    {
        if ($onRejected === null) {
            return $this;
        }

        try {
            return resolve($onRejected($this->reason));
        } catch (\Throwable|\Exception $exception) {
            return new RejectedPromise($exception);
        }
    }

    public function done(?callable $onFulfilled = null, ?callable $onRejected = null, ?callable $onProgress = null)
    {
        if ($onRejected === null) {
            throw UnhandledRejectionException::resolve($this->reason);
        }

        $result = $onRejected($this->reason);

        if ($result instanceof self) {
            throw UnhandledRejectionException::resolve($result->reason);
        }

        if ($result instanceof ExtendedPromiseInterface) {
            $result->done();
        }
    }

    public function otherwise(callable $onRejected)
    {
        if (!_checkTypehint($onRejected, $this->reason)) {
            return $this;
        }

        return $this->then(null, $onRejected);
    }

    public function always(callable $onFulfilledOrRejected)
    {
        return $this->then(null, static fn($reason) => resolve($onFulfilledOrRejected())->then(static fn() => new RejectedPromise($reason)));
    }

    public function progress(callable $onProgress)
    {
        return $this;
    }

    public function cancel() {}
}
