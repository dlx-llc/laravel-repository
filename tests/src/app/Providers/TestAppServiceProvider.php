<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Tests\App\Providers;

use Illuminate\Support\ServiceProvider;

class TestAppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }
}
