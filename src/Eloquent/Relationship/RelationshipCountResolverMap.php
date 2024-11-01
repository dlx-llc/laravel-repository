<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Eloquent\Relationship;

class RelationshipCountResolverMap
{
    /**
     * Relationship name to its resolver class map.
     *
     * @var array<string,RelationshipCountResolverContract>
     */
    protected array $map = [];

    public function __construct(protected RelationshipCountResolverContract $defaultResolver)
    {
    }

    public function set(string $relationship, RelationshipCountResolverContract $transformer): self
    {
        $this->map[$relationship] = $transformer;

        return $this;
    }

    public function get(string $relationship): RelationshipCountResolverContract
    {
        return $this->map[$relationship] ?? $this->defaultResolver;
    }
}
