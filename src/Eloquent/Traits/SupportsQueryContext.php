<?php

namespace Deluxetech\LaRepo\Eloquent\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Deluxetech\LaRepo\Contracts\CriteriaContract;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

trait SupportsQueryContext
{
    /**
     * Returns the query object.
     *
     * @return Builder
     */
    abstract protected function getQuery(): Builder;

    /**
     * The current query criteria.
     *
     * @var CriteriaContract|null
     */
    protected ?CriteriaContract $criteria = null;

    /**
     * Relation resolvers map.
     *
     * @var array[string => callable]
     */
    protected array $relationResolvers = [];

    /**
     * Relation count resolvers map.
     *
     * @var array[string => callable]
     */
    protected array $relationCountResolvers = [];

    /** @inheritdoc */
    public function addCriteria(CriteriaContract $criteria): static
    {
        if ($this->criteria) {
            $this->criteria->merge($criteria);
        } else {
            $this->criteria = $criteria;
        }

        return $this;
    }

    /** @inheritdoc */
    public function setCriteria(?CriteriaContract $criteria): static
    {
        $this->criteria = $criteria;

        return $this;
    }

    /** @inheritdoc */
    public function getCriteria(): ?CriteriaContract
    {
        return $this->criteria;
    }

    /** @inheritdoc */
    public function loadMissing(object $records, ?CriteriaContract $criteria): void
    {
        if (!$criteria) {
            return;
        } elseif (!is_a($records, Collection::class)) {
            $records = Collection::make([$records]);
        }

        $this->loadMissingAttrs($records, $criteria->getAttributes());
        $this->loadMissingRelations($records, $criteria->getRelations());
        $this->loadMissingRelationCounts($records, $criteria->getRelationCounts());
    }

    /**
     * Applies criteria on the given query.
     *
     * @param  QueryBuilder|EloquentBuilder|Relation $query
     * @param  CriteriaContract $criteria
     * @return void
     */
    protected function applyCriteria(
        QueryBuilder|EloquentBuilder|Relation $query,
        CriteriaContract $criteria
    ): void {
        if ($attrs = $criteria->getAttributes()) {
            $query->select($attrs);
        }

        if ($relations = $criteria->getRelations()) {
            $this->loadRelations($query, $relations);
        }

        if ($counts = $criteria->getRelationCounts()) {
            $this->loadRelationCounts($query, $counts);
        }

        if ($textSearch = $criteria->getTextSearch()) {
            $this->applyTextSearch($query, $textSearch);
        }

        if ($sorting = $criteria->getSorting()) {
            $this->applySorting($query, $sorting);
        }

        if ($filters = $criteria->getFilters()) {
            $this->applyFilters($query, $filters);
        }
    }

    /**
     * Loads the required relations.
     *
     * @param  object $query
     * @param  array $relations
     * @return void
     */
    protected function loadRelations(object $query, array $relations): void
    {
        foreach ($relations as $key => $value) {
            if (is_int($key)) {
                $query->with($value);
            } elseif (is_string($key)) {
                if (is_subclass_of($value, CriteriaContract::class)) {
                    $query->with($key, fn($q) => $this->applyCriteria($q, $value));
                } else {
                    $query->with($key);
                }
            }
        }
    }

    /**
     * Loads the required relation counts.
     *
     * @param  object $query
     * @param  array $counts
     * @return void
     */
    protected function loadRelationCounts(object $query, array $counts): void
    {
        $countArgs = [];

        foreach ($counts as $key => $value) {
            $relation = is_int($key) ? $value : $key;
            $countExpression = "{$relation} as {$relation}Count";

            if (is_object($value) && is_subclass_of($value, CriteriaContract::class)) {
                $countArgs[$countExpression] = fn($q) => $this->applyCriteria($q, $value);
            } else {
                $countArgs[] = $countExpression;
            }
        }

        $query->withCount($countArgs);
    }

    /**
     * Specifies the relation resolver callable.
     *
     * @param  string $relation
     * @param  callable $resolver
     * @return void
     */
    protected function setRelationResolver(string $relation, callable $resolver): void
    {
        $this->relationResolvers[$relation] = $resolver;
    }

    /**
     * Returns the relation resolver callable.
     *
     * @param  string $relation
     * @return callable
     */
    protected function getRelationResolver(string $relation): callable
    {
        return $this->relationResolvers[$relation] ?? [$this, 'loadMissingRelation'];
    }

    /**
     * Specifies the relation count resolver callable.
     *
     * @param  string $relation
     * @param  callable $resolver  Function that returns the count.
     * @return void
     */
    protected function setRelationCountResolver(string $relation, callable $resolver): void
    {
        $this->relationCountResolvers[$relation] = $resolver;
    }

    /**
     * Returns the relation count resolver callable.
     *
     * @param  string $relation
     * @return callable|null
     */
    protected function getRelationCountResolver(string $relation): ?callable
    {
        return $this->relationCountResolvers[$relation] ?? null;
    }

    /**
     * Loads missing attributes.
     *
     * @param  Collection $records
     * @param  array $attrs
     * @return void
     */
    protected function loadMissingAttrs(Collection $records, array $attrs): void
    {
        if (!$attrs || $records->isEmpty()) {
            return;
        }

        $first = $records->first();
        $idKey = $first->getKeyName();

        if (!$first->{$idKey}) {
            return;
        }

        $missing = [];
        $loaded = $first->getAttributes();

        foreach ($attrs as $attr) {
            if (!array_key_exists($attr, $loaded)) {
                $missing[] = $attr;
            }
        }

        if (!$missing) {
            return;
        }

        $ids = $records->pluck($idKey)->all();
        $missing[] = $idKey;

        $missingAttrRecords = $this->getQuery()
            ->whereIn($idKey, $ids)
            ->get($missing);

        if ($missingAttrRecords->isEmpty()) {
            return;
        }

        $records = $records->groupBy($idKey);
        $record = $missingAttrRecords->first();
        $missingAttrs = $record->getAttributes();

        foreach ($missingAttrRecords as $record) {
            $recToFill = $records[$record->{$idKey}];

            foreach ($missingAttrs as $attr => $value) {
                $recToFill->setAttribute($attr, $value);
            }
        }
    }

    /**
     * Loads missing relations.
     *
     * @param  Collection $records
     * @param  array $relations
     * @return void
     */
    protected function loadMissingRelations(Collection $records, array $relations): void
    {
        if (!$relations || $records->isEmpty()) {
            return;
        }

        foreach ($relations as $key => $value) {
            $relation = is_int($key) ? $value : $key;
            $resolver = $this->getRelationResolver($relation);
            $criteria = is_object($value) && is_subclass_of($value, CriteriaContract::class)
                ? $value
                : null;

            call_user_func_array($resolver, [$records, $relation, $criteria]);
        }
    }

    /**
     * Loads the missing relation.
     *
     * @param  Collection $records
     * @param  string $relation
     * @param  CriteriaContract|null $criteria
     * @return void
     */
    protected function loadMissingRelation(
        Collection $records,
        string $relation,
        ?CriteriaContract $criteria = null
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
                } else {
                    $loaded->add($record->{$relation});
                }
            }

            return !$relLoaded;
        });

        if ($criteria && $loaded->isNotEmpty()) {
            $this->loadMissing($loaded, $criteria);
        }

        if (
            $records->isNotEmpty() &&
            $this->relationShouldBeLoaded($records, $relation)
        ) {
            if ($criteria) {
                $records->load([
                    $relation => fn($q) => $this->applyCriteria($q, $criteria),
                ]);
            } else {
                $records->load($relation);
            }
        }
    }

    /**
     * Checks if the relation loading should be performed.
     * Foreign key check will prevent Eloquent from executing a query with a
     * false statement (...where 0 = 1...).
     *
     * @param  Collection $records
     * @param  string $relation
     * @return bool
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

    /**
     * Loads missing relation counts.
     *
     * @param  Collection $records
     * @param  array $counts
     * @return void
     */
    protected function loadMissingRelationCounts(Collection $records, array $counts): void
    {
        if (!$counts || $records->isEmpty()) {
            return;
        }

        $missing = [];
        $first = $records->first();

        foreach ($counts as $key => $value) {
            $relation = is_int($key) ? $value : $key;
            $countAttr = $relation . 'Count';
            $resolver = $this->getRelationCountResolver($relation);
            $criteria = is_object($value) && is_subclass_of($value, CriteriaContract::class)
                ? $value
                : null;

            if ($resolver) {
                call_user_func_array($resolver, [$records, $relation, $countAttr, $criteria]);
            } elseif (!isset($first->{$countAttr})) {
                $countExpression = "{$relation} as {$countAttr}";

                if ($criteria) {
                    $missing[$countExpression] = fn($q) => $this->applyCriteria($q, $criteria);
                } else {
                    $missing[] = $countExpression;
                }
            }
        }

        if (!$missing) {
            return;
        }

        $records->loadCount($missing);
    }
}
