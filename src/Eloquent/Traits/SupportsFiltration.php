<?php

namespace Deluxetech\LaRepo\Eloquent\Traits;

use Closure;
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
     * Filter operators to handler functions map.
     *
     * @var array<string, Closure>
     */
    protected array $filterHandlers = [];

    protected function registerDefaultFilterHandlers(): void
    {
        $this->setFilterHandler(FilterOperator::EXISTS, $this->applyRelationExistsFilter(...));
        $this->setFilterHandler(FilterOperator::DOES_NOT_EXIST, $this->applyRelationDoesNotExistFilter(...));
    }

    /**
     * Specifies the handler function for the given filter operator.
     */
    protected function setFilterHandler(string $operator, Closure $handler): void
    {
        $this->filterHandlers[$operator] = $handler;
    }

    /**
     * Returns the handler function for the given filter operator.
     */
    protected function getFilterHandler(string $operator): Closure
    {
        return $this->filterHandlers[$operator] ?? $this->applyFilterByDefaultStrategy(...);
    }

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

    protected function applyFilter(
        QueryBuilder|EloquentBuilder|Relation $query,
        FilterContract $filter,
    ): void {
        $operator = $filter->getOperator();
        $handler = $this->getFilterHandler($operator);

        call_user_func_array($handler, [$query, $filter]);
    }

    protected function applyFilterByDefaultStrategy(
        QueryBuilder|EloquentBuilder|Relation $query,
        FilterContract $filter,
    ): void {
        $filter = $filter->clone();

        if ($filter->getAttr()->isSegmented()) {
            $this->applyHasRelationConstraint($query, $filter);
        } else {
            $this->applyPlainFilter($query, $filter);
        }
    }

    protected function applyHasRelationConstraint(
        QueryBuilder|EloquentBuilder|Relation $query,
        FilterContract $filter,
        bool $doesNotHave = false,
    ): void {
        $attr = $filter->getAttr();
        $attrSegments = $attr->getNameSegmented();
        $relationName = array_shift($attrSegments);
        $attr->setName(...$attrSegments);

        $relation = $query->getRelation($relationName);
        $relation = $this->transformRelationship($query, $relationName, $relation);
        $relMethod = $doesNotHave ? 'whereDoesntHave' : 'whereHas';

        if ($filter->getBoolean() === BooleanOperator::OR) {
            $relMethod = 'orW' . substr($relMethod, 1);
        }

        if ($attr->getName() === '') {
            $args = [$relation];
            $filterValue = $filter->getValue();

            if (is_a($filterValue, FiltersCollectionContract::class)) {
                $args[] = fn ($subQuery) => $this->applyFilters($subQuery, $filterValue);
            }

            $query->{$relMethod}(...$args);
        } else {
            $query->{$relMethod}($relation, function ($subQuery) use ($filter) {
                $filter->setBoolean(BooleanOperator::AND);
                $this->applyFilter($subQuery, $filter, false);
            });
        }
    }

    protected function applyPlainFilter(
        QueryBuilder|EloquentBuilder|Relation $query,
        FilterContract $filter,
    ): void {
        $method = $this->preparePlainFilterMethod($filter);
        $args = $this->preparePlainFilterArgs($filter);
        $query->{$method}(...$args);
    }

    protected function applyRelationExistsFilter(
        QueryBuilder|EloquentBuilder $query,
        FilterContract $filter,
    ): void {
        $this->applyHasRelationConstraint($query, $filter->clone());
    }

    protected function applyRelationDoesNotExistFilter(
        QueryBuilder|EloquentBuilder $query,
        FilterContract $filter,
    ): void {
        $this->applyHasRelationConstraint($query, $filter->clone(), true);
    }

    /**
     * Returns query arguments for the given filter.
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
}
