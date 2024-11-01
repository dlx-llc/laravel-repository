<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Eloquent\Relationship;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

interface RelationshipTransformerContract
{
    /**
     * @param class-string<Model> $relatedModel
     */
    public function __construct(public readonly string $relatedModel = null);

    public function transform(Relation|Builder $query, Relation $relation): Relation;
}
