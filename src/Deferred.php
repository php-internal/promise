<?php

declare(strict_types=1);

namespace React\Promise;

/**
 * @template T
 * @implements PromisorInterface<T>
 */
class Deferred implements PromisorInterface
{
    /**
     * @var null|PromiseInterface<T>
     */
    private ?PromiseInterface $promise = null;

    private $resolveCallback;
    private $rejectCallback;
    private $notifyCallback;

    /** @var callable|null */
    private $canceller;

    /**
     * @param (callable(callable(T):void,callable(\Throwable):void):void)|null $canceller
     */
    public function __construct(?callable $canceller = null)
    {
        $this->canceller = $canceller;
    }

    public function promise()
    {
        if ($this->promise === null) {
            $this->promise = new Promise(function ($resolve, $reject, $notify): void {
                $this->resolveCallback = $resolve;
                $this->rejectCallback  = $reject;
                $this->notifyCallback  = $notify;
            }, $this->canceller);
            $this->canceller = null;
        }

        return $this->promise;
    }

    public function resolve($value = null)
    {
        $this->promise();

        \call_user_func($this->resolveCallback, $value);
    }

    public function reject($reason = null)
    {
        $this->promise();

        \call_user_func($this->rejectCallback, $reason);
    }

    /**
     * @deprecated 2.6.0 Progress support is deprecated and should not be used anymore.
     */
    public function notify(mixed $update = null)
    {
        $this->promise();

        \call_user_func($this->notifyCallback, $update);
    }

    /**
     * @deprecated 2.2.0
     * @see Deferred::notify()
     */
    public function progress(mixed $update = null)
    {
        $this->notify($update);
    }
}
