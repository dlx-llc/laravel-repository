<?php

namespace LaravelRepository;

use LaravelRepository\Contracts\SortingContract;
use LaravelRepository\Contracts\DataAttrContract;
use LaravelRepository\Contracts\TextSearchContract;
use LaravelRepository\Contracts\FiltersCollectionContract;
use LaravelRepository\Rules\Parsers\FiltersCollectionParser;
use LaravelRepository\Contracts\FiltersCollectionParserContract;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

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
        $this->app->singleton(FiltersCollectionParserContract::class, FiltersCollectionParser::class);

        $this->app->bind(DataAttrContract::class, DataAttr::class);
        $this->app->bind(SortingContract::class, Sorting::class);

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
