<?php

namespace Deluxetech\LaRepo\Contracts;

interface RepositoryContract extends RepositoryStrategyContract
{
    /**
     * Filters records. Simplifies access to the strategy criteria.
     * Should accept the same params as CriteriaContract::where() method.
     *
     * @return static
     * @see \Deluxetech\LaRepo\Contracts\CriteriaContract::where()
     */
    public function where(): static;

    /**
     * Filters records. Simplifies access to the strategy criteria.
     * Should accept the same params as CriteriaContract::orWhere() method.
     *
     * @return static
     * @see \Deluxetech\LaRepo\Contracts\CriteriaContract::orWhere()
     */
    public function orWhere(): static;
}
