<?php

declare(strict_types=1);

namespace React\Promise\Tests\Unit\Fixture;

/** @implements \IteratorAggregate<void, void> */
class IterableException extends \RuntimeException implements \IteratorAggregate
{
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator([]);
    }
}
