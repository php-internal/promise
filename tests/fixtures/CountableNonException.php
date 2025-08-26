<?php

declare(strict_types=1);

namespace React\Promise;

class CountableNonException implements \Countable
{
    #[\ReturnTypeWillChange]
    public function count()
    {
        return 0;
    }
}
