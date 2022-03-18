<?php

namespace Deluxetech\LaRepo\Eloquent\Traits;

use Illuminate\Database\Eloquent\Builder;
use Deluxetech\LaRepo\Eloquent\QueryHelper;
use Deluxetech\LaRepo\Contracts\SortingContract;

trait SupportsSorting
{
    /**
     * Returns the query object.
     *
     * @return Builder
     */
    abstract protected function getQuery(): Builder;

    /**
     * Applies the given sorting params on the query.
     *
     * @param  SortingContract $sorting
     * @return void
     */
    protected function applySorting(SortingContract $sorting): void
    {
        $query = $this->getQuery();
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
