<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Eloquent\TextSearch;

use Illuminate\Database\Eloquent\Builder;
use Deluxetech\LaRepo\Contracts\DataAttrContract;
use Deluxetech\LaRepo\Contracts\TextSearchContract;
use Illuminate\Database\Eloquent\Relations\Relation;
use Deluxetech\LaRepo\Eloquent\Relationship\RelationshipTransformerMap;

class TextSearchProcessor
{
    public function __construct(
        public RelationshipTransformerMap $relationshipTransformersMap,
    ) {
    }

    public function processTextSearch(Relation|Builder $query, TextSearchContract $search): void
    {
        $attrs = $search->getAttrs();
        $attrsCount = count($attrs);

        if ($attrsCount === 1) {
            $this->joinRelationOrSearch($query, $attrs[0], $search->getText(), false);
        } elseif ($attrsCount > 1) {
            $query->where(function ($query) use ($search, $attrs) {
                foreach ($attrs as $i => $attr) {
                    $this->joinRelationOrSearch($query, $attr, $search->getText(), boolval($i));
                }
            });
        }
    }

    /**
     * @param DataAttrContract|array<string> $column
     */
    protected function joinRelationOrSearch(
        Relation|Builder $query,
        DataAttrContract|array $column,
        string $text,
        bool $orCond,
    ): void {
        if ($column instanceof DataAttrContract) {
            $column = $column->getNameSegmented();
        }

        if (count($column) > 1) {
            $relationName = array_shift($column);
            $relation = $query->getRelation($relationName);

            if ($relationTransformer = $this->relationshipTransformersMap->get($relationName)) {
                $relation = $relationTransformer->transform($query, $relation);
            }

            $relMethod = $orCond ? 'orWhereHas' : 'whereHas';
            $query->{$relMethod}($relation, function ($q) use ($column, $text) {
                $this->joinRelationOrSearch($q, $column, $text, false);
            });
        } else {
            $method = $orCond ? 'orWhere' : 'where';
            $query->{$method}($column[0], 'like', '%' . $text . '%');
        }
    }
}
