<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Filters\Traits;

use Deluxetech\LaRepo\Facades\LaRepo;
use Deluxetech\LaRepo\Contracts\FilterContract;
use Deluxetech\LaRepo\Contracts\FiltersCollectionContract;

trait SanitizesFiltersCollection
{
    protected function sanitizeFiltersCollection(mixed $value): FiltersCollectionContract
    {
        if (is_a($value, FilterContract::class)) {
            return LaRepo::newFiltersCollection($this->getBoolean(), $value);
        } elseif (is_array($value)) {
            return LaRepo::newFiltersCollection($this->getBoolean(), ...$value);
        }

        return $value;
    }
}
