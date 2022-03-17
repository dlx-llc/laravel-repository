<?php

namespace Deluxetech\LaRepo;

use Illuminate\Database\Eloquent\Model;
use Deluxetech\LaRepo\Contracts\DataReaderContract;
use Deluxetech\LaRepo\Contracts\LoadContextContract;
use Deluxetech\LaRepo\Strategies\EloquentQueryStrategy;

abstract class EloquentReaderRepository implements DataReaderContract
{
    use EloquentQueryStrategy;

    /**
     * Relation resolvers map.
     *
     * @var array[string => callable]
     */
    private array $relationResolvers = [];

    /**
     * Relation count resolvers map.
     *
     * @var array[string => callable]
     */
    private array $relationCountResolvers = [];

    /**
     * Returns the eloquent model class name.
     *
     * @return string
     */
    abstract public function getModel(): string;

    /**
     * Class constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->initQuery($this->getModel());
    }

    /** @inheritdoc */
    public function setLoadContext(LoadContextContract $context): static
    {
        $this->applyLoadContext($this->strategy->getQuery(), $context);

        return $this;
    }

    /** @inheritdoc */
    public function loadMissing(object $record, LoadContextContract $context): void
    {
        $this->loadMissingAttrs($record, $context->getAttributes());
        $this->loadMissingRelations($record, $context->getRelations());
        $this->loadMissingRelationCounts($record, $context->getRelationCounts());
    }

    /**
     * Specifies the relation resolver callable.
     *
     * @param  string $relation
     * @param  callable $resolver
     * @return void
     */
    final protected function setRelationResolver(string $relation, callable $resolver): void
    {
        $this->relationResolvers[$relation] = $resolver;
    }

    /**
     * Returns the relation resolver callable.
     *
     * @param  string $relation
     * @return callable
     */
    final protected function getRelationResolver(string $relation): callable
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
    final protected function setRelationCountResolver(string $relation, callable $resolver): void
    {
        $this->relationCountResolvers[$relation] = $resolver;
    }

    /**
     * Returns the relation count resolver callable.
     *
     * @param  string $relation
     * @return callable|null
     */
    final protected function getRelationCountResolver(string $relation): ?callable
    {
        return $this->relationCountResolvers[$relation] ?? null;
    }

    /**
     * Loads missing attributes on the given model.
     *
     * @param  Model $record
     * @param  array $attrs
     * @return void
     */
    protected function loadMissingAttrs(Model $record, array $attrs): void
    {
        if (!$attrs) {
            return;
        }

        $id = $record->getKey();

        if (!$id) {
            return;
        }

        $missing = [];
        $loaded = $record->getAttributes();

        foreach ($attrs as $attr) {
            if (!isset($record->{$attr}) && !array_key_exists($attr, $loaded)) {
                $missing[] = $attr;
            }
        }

        if (!$missing) {
            return;
        }

        $missingAttrsRecord = $this->strategy->getQuery()
            ->select($missing)
            ->find($id);

        if (!$missingAttrsRecord) {
            return;
        }

        $missingAttrs = $missingAttrsRecord->getAttributes();

        foreach ($missingAttrs as $attr => $value) {
            $record->setAttribute($attr, $value);
        }
    }

    /**
     * Loads missing relations on the given model.
     *
     * @param  Model $record
     * @param  array $relations
     * @return void
     */
    protected function loadMissingRelations(Model $record, array $relations): void
    {
        if (!$relations) {
            return;
        }

        foreach ($relations as $key => $value) {
            $relation = is_int($key) ? $value : $key;

            $resolver = is_a($record, $this->getModel())
                ? $this->getRelationResolver($relation)
                : [$this, 'loadMissingRelation'];

            $loadContext = is_object($value) && is_subclass_of($value, LoadContextContract::class)
                ? $value
                : null;

            call_user_func_array($resolver, [$record, $relation, $loadContext]);
        }
    }

    /**
     * Loads the missing relation.
     *
     * @param  Model $record
     * @param  string $relation
     * @param  LoadContextContract|null $loadContext
     * @return void
     */
    protected function loadMissingRelation(
        Model $record,
        string $relation,
        ?LoadContextContract $loadContext = null
    ): void {
        if (!$loadContext) {
            $record->loadMissing($relation);
        } elseif ($record->relationLoaded($relation)) {
            $this->loadMissing($record->{$relation}, $loadContext);
        } else {
            $query = $record->{$relation}();

            if ($attrs = $loadContext->getAttributes()) {
                $query->select($attrs);
            }

            if ($counts = $loadContext->getRelationCounts()) {
                $counts = array_map(fn($r) => "$r as {$r}Count", $counts);
                $query->withCount($counts);
            }

            $relationRecord = $query->getResults();
            $record->setRelation($relation, $relationRecord);
            $subRelations = $loadContext->getRelations();

            if ($relationRecord && $subRelations) {
                $this->loadMissingRelations($relationRecord, $subRelations);
            }
        }
    }

    /**
     * Loads missing relation counts on the given model.
     *
     * @param  Model $record
     * @param  array $counts
     * @return void
     */
    protected function loadMissingRelationCounts(Model $record, array $counts): void
    {
        if (!$counts) {
            return;
        }

        $missing = [];

        foreach ($counts as $relation) {
            $countAttr = $relation . 'Count';
            $resolver = is_a($record, $this->getModel())
                ? $this->getRelationCountResolver($relation)
                : null;

            if ($resolver) {
                $count = call_user_func_array($resolver, [$record, $relation]);
                $record->{$countAttr} = is_int($count) ? $count : 0;
            } elseif (!isset($record->{$countAttr})) {
                $missing[] = "{$relation} as {$countAttr}";
            }
        }

        if (!$missing) {
            return;
        }

        $record->loadCount($missing);
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
