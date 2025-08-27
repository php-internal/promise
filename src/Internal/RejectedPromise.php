<?php

declare(strict_types=1);

namespace React\Promise\Internal;

use React\Promise\PromiseInterface;

use function React\Promise\_checkTypehint;
use function React\Promise\resolve;

/**
 * @internal
 *
 * @template-implements PromiseInterface<never>
 */
final class RejectedPromise implements PromiseInterface
{
    private bool $handled = false;
    private static ?\Closure $rejectionHandler = null;

    public function __construct(private readonly \Throwable $reason) {}

    public static function setRejectionHandler(?callable $handler): void
    {
        self::$rejectionHandler = $handler === null ? null : $handler(...);
    }

    /**
     * @template TRejected
     * @param ?(callable(\Throwable): (PromiseInterface<TRejected>|TRejected)) $onRejected
     * @return PromiseInterface<($onRejected is null ? never : TRejected)>
     */
    public function then(?callable $onFulfilled = null, ?callable $onRejected = null): PromiseInterface
    {
        if ($onRejected === null) {
            return $this;
        }

        $this->handled = true;

        try {
            return resolve($onRejected($this->reason));
        } catch (\Throwable $exception) {
            return new RejectedPromise($exception);
        }
    }

    /**
     * @template TThrowable of \Throwable
     * @template TRejected
     * @param callable(TThrowable): (PromiseInterface<TRejected>|TRejected) $onRejected
     * @return PromiseInterface<TRejected>
     */
    public function catch(callable $onRejected): PromiseInterface
    {
        if (!_checkTypehint($onRejected, $this->reason)) {
            return $this;
        }

        /**
         * @var callable(\Throwable):(PromiseInterface<TRejected>|TRejected) $onRejected
         */
        return $this->then(null, $onRejected);
    }

    public function finally(callable $onFulfilledOrRejected): PromiseInterface
    {
        return $this->then(
            null,
            static fn(\Throwable $reason): PromiseInterface => resolve($onFulfilledOrRejected())
                ->then(static fn(): PromiseInterface => new RejectedPromise($reason)),
        );
    }

    public function cancel(): void
    {
        $this->handled = true;
    }

    /**
     * @deprecated 3.0.0 Use `catch()` instead
     * @see self::catch()
     */
    public function otherwise(callable $onRejected): PromiseInterface
    {
        return $this->catch($onRejected);
    }

    /**
     * @deprecated 3.0.0 Use `always()` instead
     * @see self::always()
     */
    public function always(callable $onFulfilledOrRejected): PromiseInterface
    {
        return $this->finally($onFulfilledOrRejected);
    }

    /**
     * @throws void
     */
    public function __destruct()
    {
        if ($this->handled || self::$rejectionHandler === null) {
            return;
        }

        try {
            (self::$rejectionHandler)($this->reason);
        } catch (\Throwable $e) {
            \preg_match('/^([^:\s]++)(.*+)$/sm', (string) $e, $match);
            \assert(isset($match[1], $match[2]));
            $message = 'Fatal error: Uncaught ' . $match[1] . ' from unhandled promise rejection handler' . $match[2];

            \error_log($message);
        }
    }
}
