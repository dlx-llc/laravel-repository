<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Eloquent\Sorting;

use Illuminate\Database\Eloquent\Builder;
use Deluxetech\LaRepo\Contracts\SortingContract;
use Illuminate\Database\Eloquent\Relations\Relation;
use Deluxetech\LaRepo\Eloquent\Relationship\RelationshipTransformerMap;

class SortingProcessor
{
    public function __construct(
        public RelationshipTransformerMap $relationshipTransformersMap,
    ) {
    }

    public function processSorting(Relation|Builder $query, SortingContract $sorting): void
    {
        $direction = $sorting->getDir();
        $column = $sorting->getAttr()->getNameSegmented();

        $this->joinRelationOrSort($query, $query, $direction, $column);
    }

    protected function joinRelationOrSort(
        Relation|Builder $query,
        Relation|Builder $relationQuery,
        string $direction,
        array $column,
    ): void {
        if (count($column) > 1) {
            $relationName = array_shift($column);
            $relation = $relationQuery->getRelation($relationName);

            if ($relationTransformer = $this->relationshipTransformersMap->get($relationName)) {
                $relation = $relationTransformer->transform($query, $relation);
            }

            $query->joinRelation(
                relation: $relation,
                type: 'left',
            )->distinct();

            $relationQuery = $relation->getQuery();
            $this->joinRelationOrSort($query, $relationQuery, $direction, $column);
        } else {
            $column = $relationQuery->from . '.' . $column[0];
            $query->orderBy($column, $direction);
        }
    }
}
