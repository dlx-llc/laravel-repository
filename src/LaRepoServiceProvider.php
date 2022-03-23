<?php

namespace Deluxetech\LaRepo;

use Illuminate\Support\ServiceProvider;
use Deluxetech\LaRepo\Contracts\SortingContract;
use Deluxetech\LaRepo\Contracts\CriteriaContract;
use Deluxetech\LaRepo\Contracts\DataAttrContract;
use Deluxetech\LaRepo\Contracts\DataMapperContract;
use Deluxetech\LaRepo\Contracts\PaginationContract;
use Deluxetech\LaRepo\Contracts\TextSearchContract;
use Deluxetech\LaRepo\Contracts\FilterOptimizerContract;
use Deluxetech\LaRepo\Rules\Formatters\SortingFormatter;
use Deluxetech\LaRepo\Contracts\SortingFormatterContract;
use Deluxetech\LaRepo\Contracts\FiltersCollectionContract;
use Deluxetech\LaRepo\Rules\Formatters\TextSearchFormatter;
use Deluxetech\LaRepo\Contracts\TextSearchFormatterContract;
use Deluxetech\LaRepo\Rules\Formatters\FiltersCollectionFormatter;
use Deluxetech\LaRepo\Contracts\FiltersCollectionFormatterContract;

class LaRepoServiceProvider extends ServiceProvider
{
    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/larepo.php', 'larepo');

        $this->app->singleton(FilterOptimizerContract::class, FilterOptimizer::class);
        $this->app->singleton(SortingFormatterContract::class, SortingFormatter::class);
        $this->app->singleton(TextSearchFormatterContract::class, TextSearchFormatter::class);
        $this->app->singleton(FiltersCollectionFormatterContract::class, FiltersCollectionFormatter::class);

        $this->app->bind(DataMapperContract::class, DataMapper::class);
        $this->app->bind(SortingContract::class, Sorting::class);
        $this->app->bind(PaginationContract::class, Pagination::class);
        $this->app->bind(CriteriaContract::class, Criteria::class);

        $this->app->bind(DataAttrContract::class, function ($app, $params) {
            return new DataAttr(...$params);
        });

        $this->app->bind(TextSearchContract::class, function ($app, $params) {
            return new TextSearch(...$params);
        });

        $this->app->bind(FiltersCollectionContract::class, function ($app, $params) {
            return new FiltersCollection(...$params);
        });
    }

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'larepo');

        $this->publishes([
            __DIR__ . '/../config/larepo.php' => config_path('larepo.php')
        ], 'larepo-config');
    }
}
