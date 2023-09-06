<?php

namespace Deluxetech\LaRepo\Eloquent\Traits;

use Deluxetech\LaRepo\Contracts\SortingContract;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

trait SupportsSorting
{
    /**
     * Applies the given sorting params on the query.
     *
     * @param  QueryBuilder|EloquentBuilder|Relation $query
     * @param  SortingContract $sorting
     * @return void
     */
    protected function applySorting(
        QueryBuilder|EloquentBuilder|Relation $query,
        SortingContract $sorting
    ): void {
        $direction = $sorting->getDir();
        $column = $sorting->getAttr()->getNameSegmented();

        $this->joinRelationOrSort($query, $query, $direction, $column);
    }

    protected function joinRelationOrSort(
        QueryBuilder|EloquentBuilder|Relation $query,
        QueryBuilder|EloquentBuilder|Relation $relationQuery,
        string $direction,
        array $column
    ): void {
        if (count($column) > 1) {
            $relationName = array_shift($column);
            $relation = $relationQuery->getRelation($relationName);
            $relation = $this->transformRelationship($relationQuery, $relationName, $relation);
            $query->leftJoinRelation($relation)->distinct();

            $relationQuery = $relation->getQuery();
            $this->joinRelationOrSort($query, $relationQuery, $direction, $column);
        } else {
            $column = $relationQuery->from . '.' . $column[0];
            $query->orderBy($column, $direction);
        }
    }
}
