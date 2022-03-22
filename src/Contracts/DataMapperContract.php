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
     * Replaces domain model attributes in the criteria with the corresponding
     * source data attributes.
     *
     * @param  CriteriaContract $criteria
     * @return void
     */
    public function applyOnCriteria(CriteriaContract $criteria): void;
}
