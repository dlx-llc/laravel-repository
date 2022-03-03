<?php

namespace LaravelRepository\Contracts;

interface FilterOptimizerContract
{
    /**
     * Removes meaningless collections and groups same relation filters if possible.
     *
     * @param  FiltersCollectionContract $collection
     * @return void
     */
    public function optimize(FiltersCollectionContract $collection): void;
}
