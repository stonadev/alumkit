<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Tests;

use Alumkit\Alumkit\AlumkitServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            AlumkitServiceProvider::class,
        ];
    }
}
