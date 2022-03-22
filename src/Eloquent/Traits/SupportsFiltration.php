<?php

namespace Deluxetech\LaRepo\Eloquent\Traits;

use Deluxetech\LaRepo\Enums\FilterMode;
use Deluxetech\LaRepo\Enums\FilterOperator;
use Deluxetech\LaRepo\Contracts\FilterContract;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Deluxetech\LaRepo\Contracts\FiltersCollectionContract;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

trait SupportsFiltration
{
    /**
     * Filter handlers map.
     *
     * @var array
     */
    protected array $filterHandlers = [];

    /**
     * Registers default filter handlers.
     *
     * @return void
     */
    protected function registerDefaultFilterHandlers(): void
    {
        $this->setFilterHandler(FilterMode::EXISTS, [$this, 'applyRelationExistsFilter']);
        $this->setFilterHandler(FilterMode::DOES_NOT_EXIST, [$this, 'applyRelationDoesNotExistFilter']);
    }

    /**
     * Specifies the filter handler function.
     *
     * @param  string $mode
     * @param  callable $handler
     * @return void
     */
    protected function setFilterHandler(string $mode, callable $handler): void
    {
        $this->filterHandlers[$mode] = $handler;
    }

    /**
     * Returns the filter handler function.
     *
     * @param  string $mode
     * @return callable
     */
    protected function getFilterHandler(string $mode): callable
    {
        return $this->filterHandlers[$mode] ?? [$this, 'applyFilterByDefaultStrategy'];
    }

    /**
     * Applies the given filters on the query.
     *
     * @param  QueryBuilder|EloquentBuilder $query
     * @param  FiltersCollectionContract $filters
     * @return void
     */
    protected function applyFilters(
        QueryBuilder|EloquentBuilder $query,
        FiltersCollectionContract $filters
    ): void {
        $method = match ($filters->getOperator()) {
            FilterOperator::AND => 'where',
            FilterOperator::OR => 'orWhere',
        };

        $query->{$method}(function ($query) use ($filters) {
            foreach ($filters as $filter) {
                if (is_a($filter, FiltersCollectionContract::class)) {
                    $this->applyFilters($query, $filter);
                } else {
                    $this->applyFilter($query, $filter);
                }
            }
        });
    }

    /**
     * Applies the given filter on the given query.
     *
     * @param  QueryBuilder|EloquentBuilder $query
     * @param  FilterContract $filter
     * @return void
     */
    protected function applyFilter(
        QueryBuilder|EloquentBuilder $query,
        FilterContract $filter
    ): void {
        $mode = $filter->getMode();
        $handler = $this->getFilterHandler($mode);

        call_user_func_array($handler, [$query, $filter]);
    }

    /**
     * Applies filter using the default strategy.
     *
     * @param  QueryBuilder|EloquentBuilder $query
     * @param  FilterContract $filter
     * @return void
     */
    protected function applyFilterByDefaultStrategy(
        QueryBuilder|EloquentBuilder $query,
        FilterContract $filter
    ): void {
        $args = $this->getFilterQueryArgs($filter);
        $method = $this->getFilterQueryMethod($filter);

        if ($filter->getOperator() === FilterOperator::OR) {
            $method = 'or' . ucfirst($method);
        }

        $attr = $filter->getAttr();

        if ($attr->isSegmented()) {
            $relation = $attr->getNameExceptLastSegment();
            $query->whereHas($relation, fn($q) => $q->{$method}(...$args));
        } else {
            $query->{$method}(...$args);
        }
    }

    /**
     * Applies relation exists filter.
     *
     * @param  QueryBuilder|EloquentBuilder $query
     * @param  FilterContract $filter
     * @return void
     */
    protected function applyRelationExistsFilter(
        QueryBuilder|EloquentBuilder $query,
        FilterContract $filter
    ): void {
        $relation = $filter->getAttr()->getName();
        $query->whereHas($relation, function ($query) use ($filter) {
            $this->applyFilters($query, $filter->getValue());
        });
    }

    /**
     * Applies relation does not exist filter.
     *
     * @param  QueryBuilder|EloquentBuilder $query
     * @param  FilterContract $filter
     * @return void
     */
    protected function applyRelationDoesNotExistFilter(
        QueryBuilder|EloquentBuilder $query,
        FilterContract $filter
    ): void {
        $relation = $filter->getAttr()->getName();
        $query->whereDoesntHave($relation, function ($query) use ($filter) {
            $this->applyFilters($query, $filter->getValue());
        });
    }

    /**
     * Returns query arguments for the given filter.
     *
     * @param  FilterContract $filter
     * @return array
     */
    protected function getFilterQueryArgs(FilterContract $filter): array
    {
        $attr = $filter->getAttr()->getNameLastSegment();

        return match ($filter->getMode()) {
            FilterMode::IS_LIKE => [$attr, 'like', '%' . $filter->getValue() . '%'],
            FilterMode::IS_NOT_LIKE => [$attr, 'not like', '%' . $filter->getValue() . '%'],
            FilterMode::IS_GREATER => [$attr, '>', $filter->getValue()],
            FilterMode::IS_GREATER_OR_EQUAL => [$attr, '>=', $filter->getValue()],
            FilterMode::IS_LOWER => [$attr, '<', $filter->getValue()],
            FilterMode::IS_LOWER_OR_EQUAL => [$attr, '<=', $filter->getValue()],
            FilterMode::NOT_EQUALS_TO => [$attr, '!=', $filter->getValue()],
            FilterMode::IS_NULL => [$attr],
            FilterMode::IS_NOT_NULL => [$attr],
            default => [$attr, $filter->getValue()],
        };
    }

    /**
     * Returns the corresponding query method for the given filter.
     *
     * @param  FilterContract $filter
     * @return string
     */
    protected function getFilterQueryMethod(FilterContract $filter): string
    {
        return match ($filter->getMode()) {
            FilterMode::INCLUDED_IN => 'whereIn',
            FilterMode::NOT_INCLUDED_IN => 'whereNotIn',
            FilterMode::IN_RANGE => 'whereBetween',
            FilterMode::NOT_IN_RANGE => 'whereNotBetween',
            FilterMode::IS_NULL => 'whereNull',
            FilterMode::IS_NOT_NULL => 'whereNotNull',
            FilterMode::CONTAINS => 'whereJsonContains',
            FilterMode::DOES_NOT_CONTAIN => 'whereJsonDoesntContain',
            default => 'where',
        };
    }
}
