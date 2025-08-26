<?php

declare(strict_types=1);

namespace React\Promise;

class CountableException extends \RuntimeException implements \Countable
{
    #[\ReturnTypeWillChange]
    public function count()
    {
        return 0;
    }
}
