<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Eloquent\Relationship;

class RelationshipTransformerMap
{
    /**
     * Relationship name to its transformer class map.
     *
     * @var array<string,RelationshipTransformerContract>
     */
    protected array $map = [];

    public function set(string $relationship, RelationshipTransformerContract $transformer): self
    {
        $this->map[$relationship] = $transformer;

        return $this;
    }

    public function get(string $relationship): ?RelationshipTransformerContract
    {
        return $this->map[$relationship] ?? null;
    }
}
