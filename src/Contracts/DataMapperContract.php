<?php

namespace Deluxetech\LaRepo\Contracts;

interface DataMapperContract
{
    /**
     * Adds a link between the given domain and data attributes.
     *
     * @param  string $domainAttr
     * @param  string $dataAttr
     * @param  DataMapperContract|null $subMap
     * @return static
     */
    public function set(
        string $domainAttr,
        string $dataAttr,
        ?DataMapperContract $subMap = null
    ): static;

    /**
     * Get the matching source attribute for the given domain model attribute.
     * If there's no match, the given domain model attribute will be returned.
     *
     * @param  string $domainAttr
     * @return string
     */
    public function get(string $domainAttr): string;

    /**
     * Replaces the search criteria domain model attributes with the
     * corresponding source data attributes.
     *
     * @param  SearchCriteriaContract $criteria
     * @return void
     */
    public function applyOnSearchCriteria(SearchCriteriaContract $criteria): void;
}
