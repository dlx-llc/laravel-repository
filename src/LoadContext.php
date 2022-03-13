<?php

namespace Deluxetech\LaRepo;

use Deluxetech\LaRepo\Contracts\LoadContextContract;

class LoadContext implements LoadContextContract
{
    /**
     * @var array<string>
     */
    protected array $attributes = [];

    /**
     * @var array[string, string => LoadContext]
     */
    protected array $relations = [];

    /**
     * @var array[string, string => closure]
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
        $this->relations = $relations;

        return $this;
    }

    /** @inheritdoc */
    public function getRelations(): array
    {
        return $this->relations;
    }

    /** @inheritdoc */
    public function setRelationCounts(string ...$counts): static
    {
        $this->relationCounts = $counts;

        return $this;
    }

    /** @inheritdoc */
    public function getRelationCounts(): array
    {
        return $this->relationCounts;
    }
}
