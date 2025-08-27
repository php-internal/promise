<?php

declare(strict_types=1);

namespace React\Promise\Unit\PromiseTest;

trait FullTestTrait
{
    use PromisePendingTestTrait;
    use PromiseSettledTestTrait;
    use PromiseFulfilledTestTrait;
    use PromiseRejectedTestTrait;
    use ResolveTestTrait;
    use RejectTestTrait;
    use CancelTestTrait;
}
