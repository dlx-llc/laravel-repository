<?php

namespace Deluxetech\LaRepo\Eloquent\Traits;

use Closure;
use Illuminate\Support\Facades\App;
use Deluxetech\LaRepo\Enums\FilterOperator;
use Deluxetech\LaRepo\Enums\BooleanOperator;
use Deluxetech\LaRepo\FilterValueTransformer;
use Deluxetech\LaRepo\Contracts\FilterContract;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Deluxetech\LaRepo\Contracts\FiltersCollectionContract;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Deluxetech\LaRepo\Exceptions\InvalidFilterValueException;

/**
 * @property EloquentBuilder $query
 */
trait SupportsFiltration
{
    /**
     * Filter operators to handler functions map.
     *
     * @var array<string,Closure>
     */
    protected array $filterHandlers = [];

    protected FilterValueTransformer $filterValueTransformer;

    protected function initializeFilterValueTransformer(): void
    {
        $this->filterValueTransformer = App::make(FilterValueTransformer::class);
    }

    /**
     * Checks the model for standard datetime columns (creation, update, deletion).
     * If found, sets the corresponding transformers.
     * Also checks the model for casted date(time) attributes and sets the corresponding transformers.
     */
    protected function setDateFilterValueTransformersFromModel(): void
    {
        $model = $this->query->getModel();

        if ($model->usesTimestamps()) {
            if ($createdAtColumn = $model->getCreatedAtColumn()) {
                $this->filterValueTransformer->shouldBeDatetime($createdAtColumn);
            }

            if ($updatedAtColumn = $model->getUpdatedAtColumn()) {
                $this->filterValueTransformer->shouldBeDatetime($updatedAtColumn);
            }
        }

        if (method_exists($model, 'getDeletedAtColumn')) {
            if ($deletedAtColumn = $model->getDeletedAtColumn()) {
                $this->filterValueTransformer->shouldBeDatetime($deletedAtColumn);
            }
        }

        foreach ($model->getCasts() as $attr => $cast) {
            match ($cast) {
                'date', 'immutable_date' => $this->filterValueTransformer->shouldBeDate($attr),
                'datetime', 'immutable_datetime' => $this->filterValueTransformer->shouldBeDatetime($attr),
                default => null,
            };
        }
    }

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

    /**
     * @throws InvalidFilterValueException
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
     * @throws InvalidFilterValueException
     */
    protected function applyFilter(
        QueryBuilder|EloquentBuilder|Relation $query,
        FilterContract $filter,
    ): void {
        $operator = $filter->getOperator();
        $handler = $this->getFilterHandler($operator);

        if ($filter->hasValue()) {
            $attr = $filter->getAttr()->getName();

            if ($this->filterValueTransformer->shouldBeTransformed($attr)) {
                $filter->setValue($this->filterValueTransformer->transform($attr, $filter->getValue()));
            }
        }

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

    /**
     * @throws InvalidFilterValueException
     */
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
