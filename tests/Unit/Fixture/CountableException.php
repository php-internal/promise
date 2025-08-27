<?php

declare(strict_types=1);

namespace React\Promise\Unit\Fixture;

class CountableException extends \RuntimeException implements \Countable
{
    public function count(): int
    {
        return 0;
    }
}
