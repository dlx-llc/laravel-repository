<?php

namespace Deluxetech\LaRepo\Tests;

use Illuminate\Foundation\Application;
use Deluxetech\LaRepo\LaRepoServiceProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * @inheritdoc
     * @return MockObject
     */
    protected function createMock(string $originalClassName): MockObject
    {
        return parent::createMock($originalClassName);
    }

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
