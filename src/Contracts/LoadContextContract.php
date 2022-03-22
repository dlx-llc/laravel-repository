<?php

namespace Deluxetech\LaRepo\Contracts;

/**
 * Load context is basically an object containing information of what attributes,
 * relations and relation counts should be fetched.
 */
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
     * Adds a relation in the load context.
     *
     * @param  string $relation
     * @param  CriteriaContract|null $criteria
     * @return static
     */
    public function addRelation(string $relation, ?CriteriaContract $criteria = null): static;

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
     * Adds a relation count in the load context.
     *
     * @param  string $relation
     * @param  CriteriaContract|null $criteria
     * @return static
     */
    public function addRelationCount(string $relation, ?CriteriaContract $criteria = null): static;

    /**
     * Returns the relation counts that should be loaded.
     *
     * @return array<string>
     */
    public function getRelationCounts(): array;
}
