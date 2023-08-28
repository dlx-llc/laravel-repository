<?php

namespace Deluxetech\LaRepo\Eloquent\Traits;

use Closure;
use Deluxetech\LaRepo\Eloquent\QueryHelper;
use Deluxetech\LaRepo\Contracts\SortingContract;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

trait SupportsSorting
{
    /**
     * Returns custom relation join method if applicable.
     * The custom method should accept
     *
     * @param string $relation
     * @return ?Closure
     */
    protected function getRelationJoinMethod(string $relation): ?Closure
    {
        return null;
    }

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
        $attr = $sorting->getAttr();

        if ($attr->isSegmented()) {
            $relation = $attr->getNameExceptLastSegment();

            if ($customJoinMethod = $this->getRelationJoinMethod($relation)) {
                $customJoinMethod($query);
            } else {
                $query->leftJoinRelation($relation)->distinct();
            }

            $lastJoin = last($query->getQuery()->joins);
            $lastJoinTable = QueryHelper::instance()->tableName($lastJoin);
            $attr = $lastJoinTable . '.' . $attr->getNameLastSegment();
            $query->orderBy($attr, $sorting->getDir());
        } else {
            $query->orderBy($attr->getName(), $sorting->getDir());
        }
    }
}
