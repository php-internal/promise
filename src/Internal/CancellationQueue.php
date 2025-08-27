<?php

declare(strict_types=1);

namespace React\Promise\Internal;

/**
 * @internal
 */
final class CancellationQueue
{
    /** @var bool */
    private $started = false;

    /** @var object[] */
    private $queue = [];

    public function enqueue(mixed $cancellable): void
    {
        if (!\is_object($cancellable) || !\method_exists($cancellable, 'then') || !\method_exists($cancellable, 'cancel')) {
            return;
        }

        $length = \array_push($this->queue, $cancellable);

        if ($this->started && $length === 1) {
            $this->drain();
        }
    }

    public function __invoke(): void
    {
        if ($this->started) {
            return;
        }

        $this->started = true;
        $this->drain();
    }

    private function drain(): void
    {
        for ($i = \key($this->queue); isset($this->queue[$i]); $i++) {
            $cancellable = $this->queue[$i];
            \assert(\method_exists($cancellable, 'cancel'));

            $exception = null;

            try {
                $cancellable->cancel();
            } catch (\Throwable $exception) {
            }

            unset($this->queue[$i]);

            if ($exception) {
                throw $exception;
            }
        }

        $this->queue = [];
    }
}
