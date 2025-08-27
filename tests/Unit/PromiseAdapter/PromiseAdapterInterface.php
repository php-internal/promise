<?php

declare(strict_types=1);

namespace React\Promise\Unit\PromiseAdapter;

use React\Promise\PromiseInterface;

/**
 * @template T
 */
interface PromiseAdapterInterface
{
    /**
     * @return PromiseInterface<T>
     */
    public function promise(): PromiseInterface;

    public function resolve(mixed $value): void;

    public function reject(): void;

    public function settle(): void;
}
