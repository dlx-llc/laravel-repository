<?php

namespace Deluxetech\LaRepo;

use Deluxetech\LaRepo\Contracts\SortingContract;
use Deluxetech\LaRepo\Contracts\DataAttrContract;
use Deluxetech\LaRepo\Contracts\PaginationContract;
use Deluxetech\LaRepo\Contracts\TextSearchContract;
use Deluxetech\LaRepo\Contracts\SearchCriteriaContract;
use Deluxetech\LaRepo\Rules\Formatters\SortingFormatter;
use Deluxetech\LaRepo\Contracts\SortingFormatterContract;
use Deluxetech\LaRepo\Contracts\FiltersCollectionContract;
use Deluxetech\LaRepo\Rules\Formatters\PaginationFormatter;
use Deluxetech\LaRepo\Rules\Formatters\TextSearchFormatter;
use Deluxetech\LaRepo\Contracts\PaginationFormatterContract;
use Deluxetech\LaRepo\Contracts\TextSearchFormatterContract;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Deluxetech\LaRepo\Rules\Formatters\FiltersCollectionFormatter;
use Deluxetech\LaRepo\Contracts\FiltersCollectionFormatterContract;

class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(FilterOptimizerContract::class, FilterOptimizer::class);
        $this->app->singleton(SortingFormatterContract::class, SortingFormatter::class);
        $this->app->singleton(PaginationFormatterContract::class, PaginationFormatter::class);
        $this->app->singleton(TextSearchFormatterContract::class, TextSearchFormatter::class);
        $this->app->singleton(FiltersCollectionFormatterContract::class, FiltersCollectionFormatter::class);

        $this->app->bind(DataAttrContract::class, DataAttr::class);
        $this->app->bind(SortingContract::class, Sorting::class);
        $this->app->bind(PaginationContract::class, Pagination::class);
        $this->app->bind(SearchCriteriaContract::class, SearchCriteria::class);

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
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'lrepo');
    }
}
