<?php

namespace Deluxetech\LaRepo\Filters\Traits;

use Deluxetech\LaRepo\Facades\LaRepo;
use Deluxetech\LaRepo\Contracts\FilterContract;
use Deluxetech\LaRepo\Contracts\FiltersCollectionContract;

trait SanitizesFiltersCollection
{
    /**
     * Sanitizes filters collection value.
     *
     * @param  mixed $value
     * @return FiltersCollectionContract
     */
    protected function sanitizeFiltersCollection(mixed $value): FiltersCollectionContract
    {
        if (is_a($value, FilterContract::class)) {
            return LaRepo::newFiltersCollection($this->getBoolean(), $value);
        } elseif (is_array($value)) {
            return LaRepo::newFiltersCollection($this->getBoolean(), ...$value);
        } else {
            return $value;
        }
    }
}
