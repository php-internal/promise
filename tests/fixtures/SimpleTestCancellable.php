<?php

declare(strict_types=1);

namespace React\Promise;

class SimpleTestCancellable
{
    public $cancelCalled = false;

    public function cancel(): void
    {
        $this->cancelCalled = true;
    }
}
