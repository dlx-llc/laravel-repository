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

    /**
     * Applies the load context. Specifies the source data attributes,
     * relations and relation counts that should be loaded.
     *
     * @param  LoadContextContract $context
     * @return static
     */
    public function setLoadContext(LoadContextContract $context): static;
}
