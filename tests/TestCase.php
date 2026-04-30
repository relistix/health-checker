<?php

namespace Relistix\HealthChecker\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Relistix\HealthChecker\HealthCheckerServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            HealthCheckerServiceProvider::class,
        ];
    }
}
