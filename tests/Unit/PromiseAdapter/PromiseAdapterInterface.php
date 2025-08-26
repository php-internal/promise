<?php

declare(strict_types=1);

namespace React\Promise\Tests\Unit\PromiseAdapter;

interface PromiseAdapterInterface
{
    public function promise();

    public function resolve();

    public function reject();

    public function notify();

    public function settle();
}
