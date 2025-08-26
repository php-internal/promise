<?php

declare(strict_types=1);

namespace React\Promise\Tests\Unit\Fixture;

class CountableNonException implements \Countable
{
    #[\ReturnTypeWillChange]
    public function count()
    {
        return 0;
    }
}
