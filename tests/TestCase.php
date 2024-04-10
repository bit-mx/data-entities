<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Tests;

use BitMx\DataEntities\DataEntitiesServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            DataEntitiesServiceProvider::class,
        ];
    }
}
