<?php

namespace Deluxetech\LaRepo\Eloquent\Traits;

use Deluxetech\LaRepo\Eloquent\QueryHelper;
use Deluxetech\LaRepo\Contracts\SortingContract;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

trait SupportsSorting
{
    /**
     * Applies the given sorting params on the query.
     *
     * @param  QueryBuilder|EloquentBuilder $query
     * @param  SortingContract $sorting
     * @return void
     */
    protected function applySorting(
        QueryBuilder|EloquentBuilder $query,
        SortingContract $sorting
    ): void {
        $attr = $sorting->getAttr();

        if ($attr->isSegmented()) {
            $relation = $attr->getNameExceptLastSegment();
            $query->leftJoinRelation($relation)->distinct();
            $lastJoin = last($query->getQuery()->joins);
            $lastJoinTable = QueryHelper::instance()->tableName($lastJoin);
            $attr = $lastJoinTable . '.' . $attr->getNameLastSegment();
            $query->orderBy($attr, $sorting->getDir());
        } else {
            $query->orderBy($attr->getName(), $sorting->getDir());
        }
    }
}
