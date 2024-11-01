<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Tests;

use Deluxetech\LaRepo\LaRepoServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * @return array<class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            LaRepoServiceProvider::class,
        ];
    }
}
