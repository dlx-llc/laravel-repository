<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Contracts;

/**
 * The criteria is basically an object containing all the required information
 * to prepare a query and fetch data from a repository.
 */
interface CriteriaContract
{
    public function merge(CriteriaContract $criteria): static;

    public function clone(): static;

    public function getSorting(): ?SortingContract;

    public function setSortingRaw(string $rawStr): static;

    public function setSorting(?SortingContract $sorting): static;

    public function getTextSearch(): ?TextSearchContract;

    public function setTextSearchRaw(string $rawStr): static;

    public function setTextSearch(?TextSearchContract $textSearch): static;

    public function getFilters(): ?FiltersCollectionContract;

    public function setFiltersRaw(string $rawStr): static;

    public function setFilters(?FiltersCollectionContract $filters): static;

    /**
     * Adds a where clause to the criteria.
     */
    public function where(string $attr, mixed $operator, mixed $value = null): static;

    /**
     * Adds an or where clause to the criteria.
     */
    public function orWhere(string $attr, mixed $operator, mixed $value = null): static;

    /**
     * Specifies the attributes that should be loaded.
     */
    public function setAttributes(string ...$attributes): static;

    /**
     * Returns the specified attributes that should be loaded.
     *
     * @return array<string>
     */
    public function getAttributes(): array;

    /**
     * @param array<int|string,string|CriteriaContract|null> $relations
     */
    public function setRelations(array $relations): static;

    public function addRelation(string $relation, ?CriteriaContract $criteria = null): static;

    /**
     * @return array<string,?CriteriaContract>
     */
    public function getRelations(): array;

    /**
     * @param array<int|string,string|CriteriaContract|null> $counts
     */
    public function setRelationCounts(array $counts): static;

    public function addRelationCount(string $relation, ?CriteriaContract $criteria = null): static;

    /**
     * @return array<string,?CriteriaContract>
     */
    public function getRelationCounts(): array;
}
