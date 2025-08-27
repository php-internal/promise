<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    // uncomment to reach your current PHP version
    // ->withPhpSets(php81: true)
    // ->withSets([
    //     \Rector\PHPUnit\Set\PHPUnitSetList::PHPUNIT_100,
    // ])
    ->withTypeCoverageLevel(0)
    ->withDeadCodeLevel(0)
    ->withCodeQualityLevel(0);
