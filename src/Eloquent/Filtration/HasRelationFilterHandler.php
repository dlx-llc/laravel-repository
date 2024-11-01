<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Eloquent\Filtration;

use Illuminate\Database\Eloquent\Builder;
use Deluxetech\LaRepo\Enums\BooleanOperator;
use Deluxetech\LaRepo\Contracts\FilterContract;
use Illuminate\Database\Eloquent\Relations\Relation;
use Deluxetech\LaRepo\Contracts\FiltersCollectionContract;

class HasRelationFilterHandler implements FilterHandlerContract
{
    public function __construct(
        protected FiltersProcessor $filtersProcessor,
        protected bool $doesNotHave = false,
    ) {
    }

    /**
     * @param FilterContract<mixed> $filter
     */
    public function apply(Relation|Builder $query, FilterContract $filter): void
    {
        $attrSegments = $filter->getAttr()->getNameSegmented();
        $relationName = array_shift($attrSegments);

        if (!$relationName) {
            return;
        }

        $filter = $filter->clone();
        $attr = $filter->getAttr();
        $attr->setName(...$attrSegments);
        $relation = $query->getRelation($relationName);

        if ($relationTransformer = $this->filtersProcessor->relationshipTransformersMap->get($relationName)) {
            $relation = $relationTransformer->transform($query, $relation);
        }

        $relMethod = $this->doesNotHave ? 'whereDoesntHave' : 'whereHas';

        if ($filter->getBoolean() === BooleanOperator::OR) {
            $relMethod = 'orW' . substr($relMethod, 1);
        }

        if ($attr->getName() === '') {
            $args = [$relation];
            $filterValue = $filter->getValue();

            if (is_a($filterValue, FiltersCollectionContract::class)) {
                $args[] = fn ($subQuery) => $this->filtersProcessor->processFiltersCollection(
                    $subQuery,
                    $filterValue,
                );
            }

            $query->{$relMethod}(...$args);
        } else {
            $query->{$relMethod}($relation, function (Relation|Builder $subQuery) use ($filter) {
                $filter->setBoolean(BooleanOperator::AND);
                $this->filtersProcessor->transformFilterValue($subQuery, $filter);
                $this->filtersProcessor->processFilter($subQuery, $filter, false);
            });
        }
    }
}
