<?php

namespace Deluxetech\LaRepo\Contracts;

interface SearchCriteriaContract
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
     * Returns search criteria sorting params.
     *
     * @return SortingContract|null
     */
    public function getSorting(): ?SortingContract;

    /**
     * Specifies search criteria sorting params by raw string.
     *
     * @param  string $rawStr
     * @return static
     */
    public function setSortingRaw(string $rawStr): static;

    /**
     * Specifies search criteria sorting params.
     *
     * @param  SortingContract|null $sorting
     * @return static
     */
    public function setSorting(?SortingContract $sorting): static;

    /**
     * Returns search criteria text search params.
     *
     * @return TextSearchContract|null
     */
    public function getTextSearch(): ?TextSearchContract;

    /**
     * Specifies search criteria text search params by raw string.
     *
     * @param  string $rawStr
     * @return static
     */
    public function setTextSearchRaw(string $rawStr): static;

    /**
     * Specifies search criteria text search params.
     *
     * @param  TextSearchContract|null $textSearch
     * @return static
     */
    public function setTextSearch(?TextSearchContract $textSearch): static;

    /**
     * Returns search criteria filtration params.
     *
     * @return FiltersCollectionContract|null
     */
    public function getFilters(): ?FiltersCollectionContract;

    /**
     * Specifies search criteria filtration params by raw string.
     *
     * @param  string $rawStr
     * @return static
     */
    public function setFiltersRaw(string $rawStr): static;

    /**
     * Specifies search criteria filtration params.
     *
     * @param  FiltersCollectionContract|null $filters
     * @return static
     */
    public function setFilters(?FiltersCollectionContract $filters): static;
}
