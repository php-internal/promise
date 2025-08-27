<?php

declare(strict_types=1);

namespace React\Promise\Unit\Fixture;

class SimpleTestCancellable
{
    /** @var bool */
    public $cancelCalled = false;

    public function cancel(): void
    {
        $this->cancelCalled = true;
    }
}
