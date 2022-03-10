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

    /**
     * Specifies the data attributes mapper.
     *
     * @param  DataMapperContract|null $dataMapper
     * @return static
     */
    public function setDataMapper(?DataMapperContract $dataMapper): static;
}
