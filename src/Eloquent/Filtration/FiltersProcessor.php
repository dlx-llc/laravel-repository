<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Eloquent\Filtration;

use Illuminate\Database\Eloquent\Builder;
use Deluxetech\LaRepo\Enums\FilterOperator;
use Deluxetech\LaRepo\Enums\BooleanOperator;
use Deluxetech\LaRepo\Contracts\FilterContract;
use Illuminate\Database\Eloquent\Relations\Relation;
use Deluxetech\LaRepo\Contracts\FiltersCollectionContract;
use Deluxetech\LaRepo\Exceptions\InvalidFilterValueException;
use Deluxetech\LaRepo\Exceptions\UnsupportedFilterOperatorException;
use Deluxetech\LaRepo\Eloquent\Relationship\RelationshipTransformerMap;

class FiltersProcessor
{
    public bool $autoSetupFilterValueTransformers = true;

    public FilterHandlerMap $filterHandlerMap;
    public FilterValueTransformerMap $filterValueTransformerMap;

    public function __construct(public RelationshipTransformerMap $relationshipTransformersMap)
    {
        $this->filterValueTransformerMap = new FilterValueTransformerMap();

        $hasRelationFilterHandler = new HasRelationFilterHandler($this);
        $doesNotHaveRelationFilterHandler = new HasRelationFilterHandler($this, false);
        $plainFilterHandler = new PlainFilterHandler($this, $hasRelationFilterHandler);

        $this->filterHandlerMap = (new FilterHandlerMap())
            ->set(FilterOperator::IS_LIKE, $plainFilterHandler)
            ->set(FilterOperator::IS_NOT_LIKE, $plainFilterHandler)
            ->set(FilterOperator::EQUALS_TO, $plainFilterHandler)
            ->set(FilterOperator::NOT_EQUALS_TO, $plainFilterHandler)
            ->set(FilterOperator::INCLUDED_IN, $plainFilterHandler)
            ->set(FilterOperator::NOT_INCLUDED_IN, $plainFilterHandler)
            ->set(FilterOperator::CONTAINS, $plainFilterHandler)
            ->set(FilterOperator::DOES_NOT_CONTAIN, $plainFilterHandler)
            ->set(FilterOperator::IN_RANGE, $plainFilterHandler)
            ->set(FilterOperator::NOT_IN_RANGE, $plainFilterHandler)
            ->set(FilterOperator::IS_GREATER, $plainFilterHandler)
            ->set(FilterOperator::IS_GREATER_OR_EQUAL, $plainFilterHandler)
            ->set(FilterOperator::IS_LOWER, $plainFilterHandler)
            ->set(FilterOperator::IS_LOWER_OR_EQUAL, $plainFilterHandler)
            ->set(FilterOperator::IS_NULL, $plainFilterHandler)
            ->set(FilterOperator::IS_NOT_NULL, $plainFilterHandler)
            ->set(FilterOperator::EXISTS, $hasRelationFilterHandler)
            ->set(FilterOperator::DOES_NOT_EXIST, $doesNotHaveRelationFilterHandler);
    }

    /**
     * @throws InvalidFilterValueException
     */
    public function processFiltersCollection(
        Relation|Builder $query,
        FiltersCollectionContract $filters,
    ): void {
        $method = match ($filters->getBoolean()) {
            BooleanOperator::AND => 'where',
            BooleanOperator::OR => 'orWhere',
        };

        $query->{$method}(function (Relation|Builder $query) use ($filters) {
            foreach ($filters as $filter) {
                if (is_a($filter, FiltersCollectionContract::class)) {
                    $this->processFiltersCollection($query, $filter);
                } else {
                    $filter = $filter->clone();
                    $this->transformFilterValue($query, $filter);
                    $this->processFilter($query, $filter);
                }
            }
        });
    }

    /**
     * @throws UnsupportedFilterOperatorException
     */
    public function processFilter(
        Relation|Builder $query,
        FilterContract $filter,
    ): void {
        $operator = $filter->getOperator();
        $handler = $this->filterHandlerMap->get($operator);

        if (!$handler) {
            throw new UnsupportedFilterOperatorException($operator);
        }

        $handler->apply($query, $filter);
    }

    /**
     * @throws InvalidFilterValueException
     */
    public function transformFilterValue(
        Relation|Builder $query,
        FilterContract $filter,
    ): void {
        $model = is_a($query, Relation::class) ? $query->getRelated() : $query->getModel();
        $transformer = $this->filterValueTransformerMap->get($model);

        if (!$transformer) {
            if ($this->autoSetupFilterValueTransformers) {
                $transformer = $this->filterValueTransformerMap
                    ->create($model)
                    ->addTimestamps()
                    ->addSoftDeleteTimestamp()
                    ->addCasts();
            } else {
                return;
            }
        }

        $attr = $filter->getAttr()->getName();

        if ($filter->hasValue() && $transformer->shouldBeTransformed($attr)) {
            $value = $filter->getValue();
            $transformed = $transformer->transform($attr, $value);
            $filter->setValue($transformed);
        }
    }
}
