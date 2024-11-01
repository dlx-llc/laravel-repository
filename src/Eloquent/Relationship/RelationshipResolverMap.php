<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Eloquent\Relationship;

class RelationshipResolverMap
{
    /**
     * Relationship name to its resolver class map.
     *
     * @var array<string,RelationshipResolverContract>
     */
    protected array $map = [];

    public function __construct(protected RelationshipResolverContract $defaultResolver)
    {
    }

    public function set(string $relationship, RelationshipResolverContract $transformer): self
    {
        $this->map[$relationship] = $transformer;

        return $this;
    }

    public function get(string $relationship): RelationshipResolverContract
    {
        return $this->map[$relationship] ?? $this->defaultResolver;
    }
}
