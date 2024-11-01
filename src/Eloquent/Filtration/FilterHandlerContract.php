<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Eloquent\Filtration;

use Illuminate\Database\Eloquent\Builder;
use Deluxetech\LaRepo\Contracts\FilterContract;
use Illuminate\Database\Eloquent\Relations\Relation;

interface FilterHandlerContract
{
    /**
     * @param FilterContract<mixed> $filter
     */
    public function apply(Relation|Builder $query, FilterContract $filter): void;
}
