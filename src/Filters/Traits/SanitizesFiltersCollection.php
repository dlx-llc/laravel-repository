<?php

namespace Deluxetech\LaRepo\Filters\Traits;

use Illuminate\Support\Facades\App;
use Deluxetech\LaRepo\Contracts\FiltersCollectionContract;

trait SanitizesFiltersCollection
{
    /**
     * Sanitize a value that should be an array.
     *
     * @param  mixed $value
     * @return FiltersCollectionContract
     */
    protected function sanitizeFiltersCollection(mixed $value): FiltersCollectionContract
    {
        if (is_array($value)) {
            $params = [$this->getOperator(), $value];

            return App::makeWith(FiltersCollectionContract::class, $params);
        } else {
            return $value;
        }
    }
}
