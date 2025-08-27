<?php

declare(strict_types=1);

namespace React\Promise;

class SimpleTestCancellable
{
    /** @var bool */
    public $cancelCalled = false;

    public function cancel(): void
    {
        $this->cancelCalled = true;
    }
}
