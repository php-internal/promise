<?php

namespace React\Promise\PromiseTest;

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
