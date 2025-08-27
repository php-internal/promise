<?php

declare(strict_types=1);

namespace React\Promise\Tests\Unit\Fixture;

class SimpleFulfilledTestThenable
{
    public function then(?callable $onFulfilled = null, ?callable $onRejected = null): self
    {
        if ($onFulfilled) {
            $onFulfilled('foo');
        }

        return new self();
    }
}
