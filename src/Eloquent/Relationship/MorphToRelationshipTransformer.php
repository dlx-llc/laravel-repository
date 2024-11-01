<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Eloquent\Relationship;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class MorphToRelationshipTransformer implements RelationshipTransformerContract
{
    public function __construct(public readonly string $relatedModel = null)
    {
    }

    public function transform(Relation|Builder $query, Relation $relation): Relation
    {
        $belongsTo = Relation::noConstraints(function () use ($query, $relation) {
            return $query->getModel()->belongsTo(
                $this->relatedModel,
                $relation->getForeignKeyName(),
                $relation->getOwnerKeyName(),
            );
        });

        $belongsTo->getQuery()->mergeConstraintsFrom($relation->getQuery());

        return $belongsTo;
    }
}
