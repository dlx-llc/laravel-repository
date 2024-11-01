<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Contracts;

interface DataMapperContract
{
    /**
     * Maps the given domain attribute the the data attribute.
     */
    public function set(
        string $domainAttr,
        string $dataAttr,
        ?DataMapperContract $subMap = null,
    ): static;

    /**
     * Get the matching source attribute for the given domain model attribute.
     * If there's no match, the given domain model attribute will be returned.
     */
    public function get(string $domainAttr): string;

    /**
     * Replaces domain model attributes in the criteria with the corresponding
     * source data attributes.
     */
    public function applyOnCriteria(CriteriaContract $criteria): void;
}
