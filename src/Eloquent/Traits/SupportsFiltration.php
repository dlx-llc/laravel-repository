<?php

namespace Deluxetech\LaRepo\Eloquent\Traits;

use Deluxetech\LaRepo\Enums\FilterOperator;
use Deluxetech\LaRepo\Enums\BooleanOperator;
use Deluxetech\LaRepo\Contracts\FilterContract;
use Illuminate\Database\Eloquent\Relations\Relation;
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
        $this->setFilterHandler(FilterOperator::EXISTS, [$this, 'applyRelationExistsFilter']);
        $this->setFilterHandler(FilterOperator::DOES_NOT_EXIST, [$this, 'applyRelationDoesNotExistFilter']);
    }

    /**
     * Specifies the filter handler function.
     *
     * @param  string $operator
     * @param  callable $handler
     * @return void
     */
    protected function setFilterHandler(string $operator, callable $handler): void
    {
        $this->filterHandlers[$operator] = $handler;
    }

    /**
     * Returns the filter handler function.
     *
     * @param  string $operator
     * @return callable
     */
    protected function getFilterHandler(string $operator): callable
    {
        return $this->filterHandlers[$operator] ?? [$this, 'applyFilterByDefaultStrategy'];
    }

    /**
     * Applies the given filters on the query.
     *
     * @param  QueryBuilder|EloquentBuilder|Relation $query
     * @param  FiltersCollectionContract $filters
     * @return void
     */
    protected function applyFilters(
        QueryBuilder|EloquentBuilder|Relation $query,
        FiltersCollectionContract $filters
    ): void {
        $method = match ($filters->getBoolean()) {
            BooleanOperator::AND => 'where',
            BooleanOperator::OR => 'orWhere',
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
     * @param  QueryBuilder|EloquentBuilder|Relation $query
     * @param  FilterContract $filter
     * @return void
     */
    protected function applyFilter(
        QueryBuilder|EloquentBuilder|Relation $query,
        FilterContract $filter
    ): void {
        $operator = $filter->getOperator();
        $handler = $this->getFilterHandler($operator);

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
        QueryBuilder|EloquentBuilder|Relation $query,
        FilterContract $filter
    ): void {
        $this->applyHasRelationConstraint(
            $query,
            $filter->getBoolean(),
            $this->getFilterQueryMethod($filter),
            $this->getFilterQueryArgs($filter),
            $filter->getAttr()->getNameSegmented()
        );
    }

    protected function applyHasRelationConstraint(
        QueryBuilder|EloquentBuilder|Relation $query,
        string $boolean = BooleanOperator::AND,
        string $method,
        array $args,
        array $column
    ): void {
        if (count($column) > 1) {
            $relationName = array_shift($column);
            $relation = $query->getRelation($relationName);
            $relation = $this->transformRelationship($query, $relationName, $relation);
            $relMethod = $boolean === BooleanOperator::OR
                ? 'orWhereHas' : 'whereHas';

            $query->{$relMethod}($relation, function ($q) use ($boolean, $method, $args, $column) {
                $this->applyHasRelationConstraint($q, $boolean, $method, $args, $column);
            });
        } else {
            if ($boolean === BooleanOperator::OR) {
                $method = 'or' . ucfirst($method);
            }

            $query->{$method}(...$args);
        }
    }

    /**
     * Applies relation exists filter.
     *
     * @param  QueryBuilder|EloquentBuilder|Relation $query
     * @param  FilterContract $filter
     * @return void
     */
    protected function applyRelationExistsFilter(
        QueryBuilder|EloquentBuilder $query,
        FilterContract $filter
    ): void {
        $relation = $filter->getAttr()->getName();
        $args = [$relation];

        if ($subFilters = $filter->getValue()) {
            $args[] = fn($q) => $this->applyFilters($q, $subFilters);
        }

        $query->whereHas(...$args);
    }

    /**
     * Applies relation does not exist filter.
     *
     * @param  QueryBuilder|EloquentBuilder|Relation $query
     * @param  FilterContract $filter
     * @return void
     */
    protected function applyRelationDoesNotExistFilter(
        QueryBuilder|EloquentBuilder $query,
        FilterContract $filter
    ): void {
        $relation = $filter->getAttr()->getName();
        $args = [$relation];

        if ($subFilters = $filter->getValue()) {
            $args[] = fn($q) => $this->applyFilters($q, $subFilters);
        }

        $query->whereDoesntHave(...$args);
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

    /**
     * Returns the corresponding query method for the given filter.
     *
     * @param  FilterContract $filter
     * @return string
     */
    protected function getFilterQueryMethod(FilterContract $filter): string
    {
        return match ($filter->getOperator()) {
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
    }
}
