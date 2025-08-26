<?php

declare(strict_types=1);

namespace React\Promise;

class SimpleTestCancellableThenable
{
    public $cancelCalled = false;

    public function then(?callable $onFulfilled = null, ?callable $onRejected = null, ?callable $onProgress = null)
    {
        return new self();
    }

    public function cancel(): void
    {
        $this->cancelCalled = true;
    }
}
