<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Eloquent\Filtration;

use Illuminate\Database\Eloquent\Builder;
use Deluxetech\LaRepo\Enums\FilterOperator;
use Deluxetech\LaRepo\Enums\BooleanOperator;
use Deluxetech\LaRepo\Contracts\FilterContract;
use Illuminate\Database\Eloquent\Relations\Relation;

class PlainFilterHandler implements FilterHandlerContract
{
    public function __construct(
        protected FiltersProcessor $filtersProcessor,
        protected HasRelationFilterHandler $hasRelationFilterHandler,
    ) {
    }

    public function apply(Relation|Builder $query, FilterContract $filter): void
    {
        if ($filter->getAttr()->isSegmented()) {
            $this->hasRelationFilterHandler->apply($query, $filter);

            return;
        }

        $method = $this->preparePlainFilterMethod($filter);
        $args = $this->preparePlainFilterArgs($filter);
        $query->{$method}(...$args);
    }

    /**
     * Returns the corresponding query method for the given filter.
     */
    protected function preparePlainFilterMethod(FilterContract $filter): string
    {
        $method = match ($filter->getOperator()) {
            FilterOperator::INCLUDED_IN => 'whereIn',
            FilterOperator::NOT_INCLUDED_IN => 'whereNotIn',
            FilterOperator::IN_RANGE => 'whereBetween',
            FilterOperator::NOT_IN_RANGE => 'whereNotBetween',
            FilterOperator::IS_NULL => 'whereNull',
            FilterOperator::IS_NOT_NULL => 'whereNotNull',
            FilterOperator::CONTAINS => 'whereJsonContains',
            FilterOperator::DOES_NOT_CONTAIN => 'whereJsonDoesntContain',
            default => 'where',
        };

        if ($filter->getBoolean() === BooleanOperator::OR) {
            $method = 'or' . ucfirst($method);
        }

        return $method;
    }

    /**
     * Returns query arguments for the given filter.
     *
     * @return array{0:string,1:string,2:mixed}|array{0:string,1:mixed}
     */
    protected function preparePlainFilterArgs(FilterContract $filter): array
    {
        $attr = $filter->getAttr()->getName();

        return match ($filter->getOperator()) {
            FilterOperator::IS_LIKE => [$attr, 'like', '%' . $filter->getValue() . '%'],
            FilterOperator::IS_NOT_LIKE => [$attr, 'not like', '%' . $filter->getValue() . '%'],
            FilterOperator::IS_GREATER => [$attr, '>', $filter->getValue()],
            FilterOperator::IS_GREATER_OR_EQUAL => [$attr, '>=', $filter->getValue()],
            FilterOperator::IS_LOWER => [$attr, '<', $filter->getValue()],
            FilterOperator::IS_LOWER_OR_EQUAL => [$attr, '<=', $filter->getValue()],
            FilterOperator::NOT_EQUALS_TO => [$attr, '!=', $filter->getValue()],
            FilterOperator::IS_NULL => [$attr],
            FilterOperator::IS_NOT_NULL => [$attr],
            default => [$attr, $filter->getValue()],
        };
    }
}
