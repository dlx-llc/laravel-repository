<?php

namespace Deluxetech\LaRepo\Tests;

use Illuminate\Foundation\Application;
use Deluxetech\LaRepo\LaRepoServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Returns the package providers.
     *
     * @param  Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [LaRepoServiceProvider::class];
    }
}
