<?php

declare(strict_types=1);

namespace React\Promise\PromiseAdapter;

interface PromiseAdapterInterface
{
    public function promise();

    public function resolve();

    public function reject();

    public function notify();

    public function settle();
}
