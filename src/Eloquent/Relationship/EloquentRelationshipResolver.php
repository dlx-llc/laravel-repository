<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Eloquent\Relationship;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Deluxetech\LaRepo\Contracts\CriteriaContract;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Deluxetech\LaRepo\Eloquent\Criteria\CriteriaProcessor;

class EloquentRelationshipResolver implements RelationshipResolverContract
{
    public function __construct(public CriteriaProcessor $criteriaProcessor)
    {
    }

    public function resolveOnRecords(
        Collection $records,
        string $relation,
        ?CriteriaContract $criteria = null,
    ): void {
        if ($records->isEmpty()) {
            return;
        }

        $loaded = Collection::make();
        $records = $records->filter(function ($record) use ($relation, $loaded) {
            if ($relLoaded = $record->relationLoaded($relation)) {
                if (is_a($record->{$relation}, Collection::class)) {
                    foreach ($record->{$relation} as $relRecord) {
                        $loaded->add($relRecord);
                    }
                } elseif ($record->{$relation}) {
                    $loaded->add($record->{$relation});
                }
            }

            return !$relLoaded;
        });

        if ($criteria && $loaded->isNotEmpty()) {
            $this->criteriaProcessor->loadMissing($loaded, $criteria);
        }

        if (
            $records->isNotEmpty() &&
            $this->relationShouldBeLoaded($records, $relation)
        ) {
            if ($criteria) {
                $records->load([
                    $relation => fn (Relation|Builder $relationQuery) => $this->criteriaProcessor
                        ->processCriteria($relationQuery, $criteria),
                ]);
            } else {
                $records->load($relation);
            }
        }
    }

    public function resolveOnQuery(
        Relation|Builder $query,
        string $relation,
        ?CriteriaContract $criteria = null,
    ): void {
        if ($criteria) {
            $query->with(
                $relation,
                fn (Relation|Builder $relationQuery) => $this->criteriaProcessor
                    ->processCriteria($relationQuery, $criteria),
            );
        } else {
            $query->with($relation);
        }
    }

    /**
     * Checks if the relation loading should be performed.
     * Foreign key check will prevent Eloquent from executing an unnecessary
     * query with an obvious false statement (...where 0 = 1...).
     *
     * @param Collection<int,TResult> $records
     */
    protected function relationShouldBeLoaded(Collection $records, string $relation): bool
    {
        $relationQuery = $records->first()->{$relation}();

        if (is_a($relationQuery, BelongsTo::class)) {
            $fk = $records->first()->{$relation}()->getForeignKeyName();

            foreach ($records as $record) {
                if (!is_null($record->{$fk})) {
                    return true;
                }
            }

            return false;
        }

        return true;
    }
}
