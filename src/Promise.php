<?php

declare(strict_types=1);

namespace React\Promise;

/**
 * @template T
 * @implements ExtendedPromiseInterface<T>
 * @implements CancellablePromiseInterface<T>
 */
class Promise implements ExtendedPromiseInterface, CancellablePromiseInterface
{
    /** @var (callable(callable(T): void, callable(\Throwable): void): void)|null */
    private $canceller;

    /** @var ?PromiseInterface<T> */
    private ?PromiseInterface $result = null;

    /** @var list<callable(PromiseInterface<T>): void> */
    private array $handlers = [];

    /** @var list<callable> */
    private array $progressHandlers = [];

    /** @var int<0, max> */
    private int $requiredCancelRequests = 0;

    /** @var int<0, max> */
    private int $cancelRequests = 0;

    public function __construct(callable $resolver, ?callable $canceller = null)
    {
        $this->canceller = $canceller;

        // Explicitly overwrite arguments with null values before invoking
        // resolver function. This ensure that these arguments do not show up
        // in the stack trace in PHP 7+ only.
        $cb = $resolver;
        $resolver = $canceller = null;
        $this->call($cb);
    }

    public function then(?callable $onFulfilled = null, ?callable $onRejected = null, ?callable $onProgress = null)
    {
        if ($this->result !== null) {
            return $this->result->then($onFulfilled, $onRejected, $onProgress);
        }

        if ($this->canceller === null) {
            return new static($this->resolver($onFulfilled, $onRejected, $onProgress));
        }

        // This promise has a canceller, so we create a new child promise which
        // has a canceller that invokes the parent canceller if all other
        // followers are also cancelled. We keep a reference to this promise
        // instance for the static canceller function and clear this to avoid
        // keeping a cyclic reference between parent and follower.
        $parent = $this;
        ++$parent->requiredCancelRequests;

        return new static(
            $this->resolver($onFulfilled, $onRejected, $onProgress),
            static function () use (&$parent): void {
                if (++$parent->cancelRequests >= $parent->requiredCancelRequests) {
                    $parent->cancel();
                }

                $parent = null;
            },
        );
    }

    public function done(?callable $onFulfilled = null, ?callable $onRejected = null, ?callable $onProgress = null)
    {
        if ($this->result !== null) {
            return $this->result->done($onFulfilled, $onRejected, $onProgress);
        }

        $this->handlers[] = static function (ExtendedPromiseInterface $promise) use ($onFulfilled, $onRejected): void {
            $promise
                ->done($onFulfilled, $onRejected);
        };

        if ($onProgress) {
            $this->progressHandlers[] = $onProgress;
        }
    }

    public function otherwise(callable $onRejected)
    {
        return $this->then(null, static function ($reason) use ($onRejected) {
            if (!_checkTypehint($onRejected, $reason)) {
                return new RejectedPromise($reason);
            }

            return $onRejected($reason);
        });
    }

    public function always(callable $onFulfilledOrRejected)
    {
        return $this->then(static fn($value) => resolve($onFulfilledOrRejected())->then(static fn() => $value), static fn($reason) => resolve($onFulfilledOrRejected())->then(static fn() => new RejectedPromise($reason)));
    }

    public function progress(callable $onProgress)
    {
        return $this->then(null, null, $onProgress);
    }

    public function cancel()
    {
        if ($this->canceller === null || $this->result !== null) {
            return;
        }

        $canceller = $this->canceller;
        $this->canceller = null;

        $this->call($canceller);
    }

    private function resolver(?callable $onFulfilled = null, ?callable $onRejected = null, ?callable $onProgress = null)
    {
        return function ($resolve, $reject, $notify) use ($onFulfilled, $onRejected, $onProgress): void {
            if ($onProgress) {
                $progressHandler = static function ($update) use ($notify, $onProgress): void {
                    try {
                        $notify($onProgress($update));
                    } catch (\Throwable|\Exception $e) {
                        $notify($e);
                    }
                };
            } else {
                $progressHandler = $notify;
            }

            $this->handlers[] = static function (ExtendedPromiseInterface $promise) use ($onFulfilled, $onRejected, $resolve, $reject, $progressHandler): void {
                $promise
                    ->then($onFulfilled, $onRejected)
                    ->done($resolve, $reject, $progressHandler);
            };

            $this->progressHandlers[] = $progressHandler;
        };
    }

    private function reject($reason = null): void
    {
        if ($this->result !== null) {
            return;
        }

        $this->settle(reject($reason));
    }

    private function settle(ExtendedPromiseInterface $promise): void
    {
        $promise = $this->unwrap($promise);

        if ($promise === $this) {
            $promise = new RejectedPromise(
                new \LogicException('Cannot resolve a promise with itself.'),
            );
        }

        $handlers = $this->handlers;

        $this->progressHandlers = $this->handlers = [];
        $this->result = $promise;
        $this->canceller = null;

        foreach ($handlers as $handler) {
            $handler($promise);
        }
    }

    private function unwrap($promise)
    {
        $promise = $this->extract($promise);

        while ($promise instanceof self && $promise->result !== null) {
            $promise = $this->extract($promise->result);
        }

        return $promise;
    }

    private function extract($promise)
    {
        if ($promise instanceof LazyPromise) {
            $promise = $promise->promise();
        }

        return $promise;
    }

    private function call(callable $cb): void
    {
        // Explicitly overwrite argument with null value. This ensure that this
        // argument does not show up in the stack trace in PHP 7+ only.
        $callback = $cb;
        $cb = null;

        // Use reflection to inspect number of arguments expected by this callback.
        // We did some careful benchmarking here: Using reflection to avoid unneeded
        // function arguments is actually faster than blindly passing them.
        // Also, this helps avoiding unnecessary function arguments in the call stack
        // if the callback creates an Exception (creating garbage cycles).
        if (\is_array($callback)) {
            $ref = new \ReflectionMethod($callback[0], $callback[1]);
        } elseif (\is_object($callback) && !$callback instanceof \Closure) {
            $ref = new \ReflectionMethod($callback, '__invoke');
        } else {
            $ref = new \ReflectionFunction($callback);
        }
        $args = $ref->getNumberOfParameters();

        try {
            if ($args === 0) {
                $callback();
            } else {
                // Keep references to this promise instance for the static resolve/reject functions.
                // By using static callbacks that are not bound to this instance
                // and passing the target promise instance by reference, we can
                // still execute its resolving logic and still clear this
                // reference when settling the promise. This helps avoiding
                // garbage cycles if any callback creates an Exception.
                // These assumptions are covered by the test suite, so if you ever feel like
                // refactoring this, go ahead, any alternative suggestions are welcome!
                $target = & $this;
                $progressHandlers = & $this->progressHandlers;

                $callback(
                    static function ($value = null) use (&$target): void {
                        if ($target !== null) {
                            $target->settle(resolve($value));
                            $target = null;
                        }
                    },
                    static function ($reason = null) use (&$target): void {
                        if ($target !== null) {
                            $target->reject($reason);
                            $target = null;
                        }
                    },
                    static function ($update = null) use (&$progressHandlers): void {
                        foreach ($progressHandlers as $handler) {
                            $handler($update);
                        }
                    },
                );
            }
        } catch (\Throwable|\Exception $e) {
            $target = null;
            $this->reject($e);
        }
    }
}
