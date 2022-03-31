<?php

namespace Deluxetech\LaRepo\Contracts;

use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Illuminate\Contracts\Pagination\Paginator;

interface RepositoryStrategyContract
{
    /**
     * Adds query criteria if not set or merges with the existing criteria.
     *
     * @param  CriteriaContract $criteria
     * @return static
     */
    public function addCriteria(CriteriaContract $criteria): static;

    /**
     * Specifies the query criteria.
     *
     * @param  CriteriaContract|null $criteria
     * @return static
     */
    public function setCriteria(?CriteriaContract $criteria): static;

    /**
     * Returns the current query criteria.
     *
     * @return ?CriteriaContract
     */
    public function getCriteria(): ?CriteriaContract;

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
     * Resets the query to its initial state.
     *
     * @return static
     */
    public function reset(): static;

    /**
     * Adds a function that will be called before fetching data.
     *
     * @param  callable $callback
     * @return static
     */
    public function addFetchCallback(callable $callback): static;

    /**
     * Removes all the fetch callbacks specified before.
     *
     * @return static
     */
    public function clearFetchCallbacks(): static;

    /**
     * Fetches results.
     *
     * @return Collection
     */
    public function get(): Collection;

    /**
     * Fetches results via lazy collection.
     *
     * @return LazyCollection
     */
    public function cursor(): LazyCollection;

    /**
     * Fetches results in chunks via lazy collection.
     *
     * @param  int $chunkSize
     * @return LazyCollection
     */
    public function lazy(int $chunkSize = 1000): LazyCollection;

    /**
     * Fetches paginated results.
     *
     * @param  PaginationContract $pagination
     * @return Paginator
     */
    public function paginate(PaginationContract $pagination): Paginator;

    /**
     * Returns the number of records matching the query.
     *
     * @return int
     */
    public function count(): int;

    /**
     * Checks if records matching the query exist.
     *
     * @return bool
     */
    public function exists(): bool;

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
     * Loads missing parameters in accordance with the given criteria.
     *
     * @param  object $records
     * @param  CriteriaContract $criteria
     * @return void
     */
    public function loadMissing(object $records, CriteriaContract $criteria): void;
}
