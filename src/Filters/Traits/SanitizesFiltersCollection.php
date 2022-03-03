<?php

namespace LaravelRepository\Filters\Traits;

use Illuminate\Support\Facades\App;
use LaravelRepository\Contracts\FiltersCollectionContract;

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
        $params = [$this->getOperator(), $value];

        return App::makeWith(FiltersCollectionContract::class, $params);
    }
}
