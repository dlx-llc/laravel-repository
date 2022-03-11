<?php

namespace Deluxetech\LaRepo\Contracts;

interface LoadContextContract
{
    /**
     * Specifies the attributes that should be loaded.
     *
     * @param  string ...$attributes
     * @return static
     */
    public function setAttributes(string ...$attributes): static;

    /**
     * Returns the attributes that should be loaded.
     *
     * @return array<string>
     */
    public function getAttributes(): array;

    /**
     * Specifies the relations that should be loaded.
     *
     * @param  array $relations
     * @return static
     */
    public function setRelations(array $relations): static;

    /**
     * Returns the relations that should be loaded.
     *
     * @return array
     */
    public function getRelations(): array;

    /**
     * Specifies the relation counts that should be loaded.
     *
     * @param  array $counts
     * @return static
     */
    public function setRelationCounts(array $counts): static;

    /**
     * Returns the relation counts that should be loaded.
     *
     * @return array
     */
    public function getRelationCounts(): array;
}
