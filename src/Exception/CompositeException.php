<?php

namespace React\Promise\Exception;

/**
 * Represents an exception that is a composite of one or more other exceptions.
 *
 * This exception is useful in situations where a promise must be rejected
 * with multiple exceptions. It is used for example to reject the returned
 * promise from `some()` and `any()` when too many input promises reject.
 */
class CompositeException extends \Exception
{
    /** @param \Throwable[] $throwables */
    public function __construct(private readonly array $throwables, string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return \Throwable[]
     */
    public function getThrowables(): array
    {
        return $this->throwables;
    }
}
