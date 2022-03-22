<?php

namespace Deluxetech\LaRepo\Contracts;

/**
 * The criteria is basically an object containing all the required information
 * to prepare a query and fetch data from a repository.
 */
interface CriteriaContract
{
    /**
     * Class constructor.
     *
     * @param  TextSearchContract|string|null $textSearch
     * @param  SortingContract|string|null $sorting
     * @param  FiltersCollectionContract|string|null $filters
     * @return void
     */
    public function __construct(
        TextSearchContract|string|null $textSearch = null,
        SortingContract|string|null $sorting = null,
        FiltersCollectionContract|string|null $filters = null
    );

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
     * Specifies filtration params.
     *
     * @param  FiltersCollectionContract|null $filters
     * @return static
     */
    public function setFilters(?FiltersCollectionContract $filters): static;
}
