<?php

namespace Deluxetech\LaRepo\Contracts;

use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Illuminate\Contracts\Pagination\Paginator;

interface DataReaderContract
{
    /**
     * Specifies the number of results that should be skipped.
     *
     * @param  int $count
     * @return static
     */
    public function offset(int $offset): static;

    /**
     * Limits the number of results.
     *
     * @param  int $count
     * @return static
     */
    public function limit(int $count): static;

    /**
     * Applies the search criteria.
     *
     * @param  SearchCriteriaContract $criteria
     * @return static
     */
    public function search(SearchCriteriaContract $criteria): static;

    /**
     * Resets the query object to its initial state.
     *
     * @return static
     */
    public function reset(): static;

    /**
     * Fetches query results.
     *
     * @return Collection
     */
    public function get(): Collection;

    /**
     * Fetches paginated query results.
     *
     * @param  PaginationContract $pagination
     * @return Paginator
     */
    public function paginate(PaginationContract $pagination): Paginator;

    /**
     * Fetches query results via lazy collection.
     *
     * @return LazyCollection
     */
    public function cursor(): LazyCollection;

    /**
     * Fetches query results in chunks via lazy collection.
     *
     * @param  int $chunkSize
     * @return LazyCollection
     */
    public function lazy(int $chunkSize = 1000): LazyCollection;

    /**
     * Returns the number of records matching the query.
     *
     * @return int
     */
    public function count(): int;

    /**
     * Fetches a single record by ID.
     *
     * @param  int|string $id
     * @return object|null
     */
    public function find(int|string $id): ?object;

    /**
     * Fetches the first matching record.
     *
     * @return object|null
     */
    public function first(): ?object;

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

    /**
     * Loads missing parameters in the record in accordance with the load context.
     *
     * @param  object $record
     * @param  LoadContextContract $context
     * @return void
     */
    public function loadMissing(object $record, LoadContextContract $context): void;
}
