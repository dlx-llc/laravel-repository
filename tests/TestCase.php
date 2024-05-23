<?php

namespace Deluxetech\LaRepo\Tests;

use Deluxetech\LaRepo\LaRepoServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [LaRepoServiceProvider::class];
    }
}
