<?php

declare(strict_types=1);

namespace React\Promise;

class UnhandledRejectionException extends \RuntimeException
{
    public function __construct(private $reason)
    {
        $message = \sprintf('Unhandled Rejection: %s', \json_encode($reason));

        parent::__construct($message, 0);
    }

    public static function resolve($reason)
    {
        if ($reason instanceof \Exception || $reason instanceof \Throwable) {
            return $reason;
        }

        return new static($reason);
    }

    public function getReason()
    {
        return $this->reason;
    }
}
