<?php

namespace Deluxetech\LaRepo\Contracts;

/**
 * The criteria is basically an object containing all the required information
 * to prepare a query and fetch data from a repository.
 */
interface CriteriaContract
{
    /**
     * Merges query criteria.
     *
     * @param  CriteriaContract $criteria
     * @return static
     */
    public function merge(CriteriaContract $criteria): static;

    /**
     * Returns the sorting params.
     *
     * @return SortingContract|null
     */
    public function getSorting(): ?SortingContract;

    /**
     * Specifies sorting params using a raw string.
     *
     * @param  string $rawStr
     * @return static
     */
    public function setSortingRaw(string $rawStr): static;

    /**
     * Specifies sorting params.
     *
     * @param  SortingContract|null $sorting
     * @return static
     */
    public function setSorting(?SortingContract $sorting): static;

    /**
     * Returns text search params.
     *
     * @return TextSearchContract|null
     */
    public function getTextSearch(): ?TextSearchContract;

    /**
     * Specifies text search params using a raw string.
     *
     * @param  string $rawStr
     * @return static
     */
    public function setTextSearchRaw(string $rawStr): static;

    /**
     * Specifies text search params.
     *
     * @param  TextSearchContract|null $textSearch
     * @return static
     */
    public function setTextSearch(?TextSearchContract $textSearch): static;

    /**
     * Returns filtration params.
     *
     * @return FiltersCollectionContract|null
     */
    public function getFilters(): ?FiltersCollectionContract;

    /**
     * Specifies filtration params using a raw string.
     *
     * @param  string $rawStr
     * @return static
     */
    public function setFiltersRaw(string $rawStr): static;

    /**
     * Adds a where clause to the criteria.
     *
     * @param  string $attr
     * @param  string $operator
     * @param  mixed  $value
     * @return static
     */
    public function where(string $attr, string $operator, mixed $value = null): static;

    /**
     * Adds an or where clause to the criteria.
     *
     * @param  string $attr
     * @param  string $operator
     * @param  mixed  $value
     * @return static
     */
    public function orWhere(string $attr, string $operator, mixed $value = null): static;

    /**
     * Specifies filtration params.
     *
     * @param  FiltersCollectionContract|null $filters
     * @return static
     */
    public function setFilters(?FiltersCollectionContract $filters): static;

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
     * Adds a relation to load.
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
     * Adds a relation count to load.
     *
     * @param  string $relation
     * @param  CriteriaContract|null $criteria
     * @return static
     */
    public function addRelationCount(string $relation, ?CriteriaContract $criteria = null): static;

    /**
     * Returns the relation counts that should be loaded.
     *
     * @return array
     */
    public function getRelationCounts(): array;
}
