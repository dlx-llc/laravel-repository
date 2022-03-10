<?php

namespace Deluxetech\LaRepo\Contracts;

interface ImmutableRepositoryContract extends DataReaderContract
{
    /**
     * Specifies the repository strategy.
     *
     * @param  RepositoryStrategyContract $strategy
     * @return static
     */
    public function setStrategy(RepositoryStrategyContract $strategy): static;
}
