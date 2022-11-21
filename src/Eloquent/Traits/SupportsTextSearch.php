<?php

namespace Deluxetech\LaRepo\Eloquent\Traits;

use Deluxetech\LaRepo\Contracts\DataAttrContract;
use Deluxetech\LaRepo\Contracts\TextSearchContract;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

trait SupportsTextSearch
{
    /**
     * Applies the given text search params on the query.
     *
     * @param  QueryBuilder|EloquentBuilder|Relation $query
     * @param  TextSearchContract $search
     * @return void
     */
    protected function applyTextSearch(
        QueryBuilder|EloquentBuilder|Relation $query,
        TextSearchContract $search
    ): void {
        $attrs = $search->getAttrs();
        $attrsCount = count($attrs);

        if ($attrsCount === 1) {
            $this->searchForText($query, $attrs[0], $search->getText(), false);
        } elseif ($attrsCount > 1) {
            $query->where(function ($query) use ($search, $attrs) {
                foreach ($attrs as $i => $attr) {
                    $this->searchForText($query, $attr, $search->getText(), boolval($i));
                }
            });
        }
    }

    /**
     * Searches for the given text in the given query's data attribute.
     *
     * @param  QueryBuilder|EloquentBuilder|Relation $query
     * @param  DataAttrContract $attr
     * @param  string $text
     * @param  bool $orCond
     * @return void
     */
    protected function searchForText(
        QueryBuilder|EloquentBuilder|Relation $query,
        DataAttrContract $attr,
        string $text,
        bool $orCond
    ): void {
        $field = $attr->getNameLastSegment();
        $method = $orCond ? 'orWhere' : 'where';
        $args = [$field, 'like', '%' . $text . '%'];

        if ($attr->isSegmented()) {
            $method .= 'Has';
            $relation = $attr->getNameExceptLastSegment();
            $query->{$method}($relation, fn($q) => $q->where(...$args));
        } else {
            $query->{$method}(...$args);
        }
    }
}
