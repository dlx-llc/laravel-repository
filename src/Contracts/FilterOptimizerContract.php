<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Contracts;

interface FilterOptimizerContract
{
    /**
     * Removes meaningless collections and groups same relation filters if possible.
     */
    public function optimize(FiltersCollectionContract $collection): void;
}
