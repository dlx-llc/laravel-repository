<?php

namespace Deluxetech\LaRepo\Eloquent\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Deluxetech\LaRepo\Contracts\LoadContextContract;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait SupportsContextLoading
{
    /**
     * Returns the query object.
     *
     * @return Builder
     */
    abstract protected function getQuery(): Builder;

    /**
     * Returns the eloquent model class name.
     *
     * @return string
     */
    abstract public function getModel(): string;

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
    public function setLoadContext(LoadContextContract $context): static
    {
        $this->applyLoadContext($this->query, $context);

        return $this;
    }

    /** @inheritdoc */
    public function loadMissing(object $records, LoadContextContract $context): void
    {
        if (is_a($records, Model::class)) {
            $records = Collection::make([$records]);
        }

        $this->loadMissingAttrs($records, $context->getAttributes());
        $this->loadMissingRelations($records, $context->getRelations());
        $this->loadMissingRelationCounts($records, $context->getRelationCounts());
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

        $sameModel = is_a($records->first(), $this->getModel());

        foreach ($relations as $key => $value) {
            $relation = is_int($key) ? $value : $key;

            $resolver = $sameModel
                ? $this->getRelationResolver($relation)
                : [$this, 'loadMissingRelation'];

            $loadContext = is_object($value) && is_subclass_of($value, LoadContextContract::class)
                ? $value
                : null;

            call_user_func_array($resolver, [$records, $relation, $loadContext]);
        }
    }

    /**
     * Loads the missing relation.
     *
     * @param  Collection $records
     * @param  string $relation
     * @param  LoadContextContract|null $loadContext
     * @return void
     */
    protected function loadMissingRelation(
        Collection $records,
        string $relation,
        ?LoadContextContract $loadContext = null
    ): void {
        if ($records->isEmpty()) {
            return;
        } else {
            $loaded = Collection::make();
            $records = $records->filter(function ($record) use ($relation, $loaded) {
                if ($relLoaded = $record->relationLoaded($relation)) {
                    $loaded->add($record);
                }

                return !$relLoaded;
            });

            if ($loaded->isNotEmpty()) {
                $this->loadMissing($loaded, $loadContext);
            }

            if ($records->isEmpty()) {
                return;
            }

            if (!$this->relationShouldBeLoaded($records, $relation)) {
                return;
            }

            if ($loadContext) {
                $records->load([
                    $relation => function ($query) use ($loadContext) {
                        if ($attrs = $loadContext->getAttributes()) {
                            $query->select($attrs);
                        }

                        if ($counts = $loadContext->getRelationCounts()) {
                            $counts = array_map(fn($r) => "$r as {$r}Count", $counts);
                            $query->withCount($counts);
                        }
                    },
                ]);

                if ($subRelations = $loadContext->getRelations()) {
                    $relationRecords = Collection::make();

                    foreach ($records as $record) {
                        $value = $record->{$relation};

                        if (!is_null($value)) {
                            if (is_a($value, Collection::class)) {
                                foreach ($value as $item) {
                                    $relationRecords->add($item);
                                }
                            } else {
                                $relationRecords->add($value);
                            }
                        }
                    }

                    if ($relationRecords->isNotEmpty()) {
                        $this->loadMissingRelations($relationRecords, $subRelations);
                    }
                }
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
        $sameModel = is_a($first, $this->getModel());

        foreach ($counts as $relation) {
            $countAttr = $relation . 'Count';
            $resolver = $sameModel
                ? $this->getRelationCountResolver($relation)
                : null;

            if ($resolver) {
                call_user_func_array($resolver, [$records, $relation, $countAttr]);
            } elseif (!isset($first->{$countAttr})) {
                $missing[] = "{$relation} as {$countAttr}";
            }
        }

        if (!$missing) {
            return;
        }

        $records->loadCount($missing);
    }

    /**
     * Recursively loads the required relations.
     *
     * @param  object $query
     * @param  LoadContextContract $context
     * @return void
     */
    protected function applyLoadContext(object $query, LoadContextContract $context): void
    {
        if ($attrs = $context->getAttributes()) {
            $query->select($attrs);
        }

        foreach ($context->getRelations() as $key => $value) {
            if (is_int($key)) {
                $query->with($value);
            } elseif (is_string($key)) {
                if (is_subclass_of($value, LoadContextContract::class)) {
                    $query->with($key, function ($query) use ($value) {
                        $this->applyLoadContext($query, $value);
                    });
                } else {
                    $query->with($key);
                }
            }
        }

        if ($counts = $context->getRelationCounts()) {
            $counts = array_map(fn($r) => "$r as {$r}Count", $counts);
            $query->withCount($counts);
        }
    }
}
