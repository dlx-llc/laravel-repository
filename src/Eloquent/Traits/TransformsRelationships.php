<?php

namespace Deluxetech\LaRepo\Eloquent\Traits;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

trait TransformsRelationships
{
    /**
     * Override this method to transform relationships.
     *
     * @param EloquentBuilder $query
     * @param string $relationName
     * @param Relation $relation
     * @return Relation
     */
    protected function transformRelationship(
        EloquentBuilder $query,
        string $relationName,
        Relation $relation
    ): Relation {
        if (is_a($relation, MorphTo::class)) {
            return $this->transformMorphToRelationship(
                $query,
                $relationName,
                $relation
            );
        }

        return $relation;
    }

    protected function transformMorphToRelationship(
        EloquentBuilder $query,
        string $relationName,
        Relation $relation
    ): Relation {
        $type = $this->getMorphToRelationshipType($relation, $relationName);

        if (!$type) {
            return $relation;
        }

        $belongsTo = Relation::noConstraints(function () use ($query, $type, $relation) {
            return $query->getModel()->belongsTo(
                $type,
                $relation->getForeignKeyName(),
                $relation->getOwnerKeyName()
            );
        });

        $belongsTo->getQuery()->mergeConstraintsFrom($relation->getQuery());

        return $belongsTo;
    }

    protected function getMorphToRelationshipType(
        MorphTo $relation,
        string $relationName
    ): ?string {
        return null;
    }
}
