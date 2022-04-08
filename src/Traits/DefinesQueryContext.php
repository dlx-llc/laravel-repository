<?php

namespace Deluxetech\LaRepo\Traits;

use Deluxetech\LaRepo\Contracts\CriteriaContract;

trait DefinesQueryContext
{
    /**
     * @var array<string>
     */
    protected array $attributes = [];

    /**
     * @var array[string, string => CriteriaContract|null]
     */
    protected array $relations = [];

    /**
     * @var array[string, string => CriteriaContract|null]
     */
    protected array $relationCounts = [];

    /** @inheritdoc */
    public function setAttributes(string ...$attributes): static
    {
        $this->attributes = $attributes;

        return $this;
    }

    /** @inheritdoc */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /** @inheritdoc */
    public function setRelations(array $relations): static
    {
        foreach ($relations as $key => $value) {
            if (is_int($key)) {
                $this->addRelation($value);
            } else {
                $this->addRelation($key, $value);
            }
        }

        return $this;
    }

    /** @inheritdoc */
    public function addRelation(string $relation, ?CriteriaContract $criteria = null): static
    {
        $this->relations[$relation] = $criteria;

        return $this;
    }

    /** @inheritdoc */
    public function getRelations(): array
    {
        return $this->relations;
    }

    /** @inheritdoc */
    public function setRelationCounts(array $counts): static
    {
        foreach ($counts as $relation => $criteria) {
            if (is_int($relation)) {
                $relation = $criteria;
                $criteria = null;
            }

            $this->addRelationCount($relation, $criteria);
        }

        return $this;
    }

    /** @inheritdoc */
    public function addRelationCount(string $relation, ?CriteriaContract $criteria = null): static
    {
        $this->relationCounts[$relation] = $criteria;

        return $this;
    }

    /** @inheritdoc */
    public function getRelationCounts(): array
    {
        return $this->relationCounts;
    }
}
