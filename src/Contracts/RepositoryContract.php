<?php

namespace Deluxetech\LaRepo\Contracts;

use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Illuminate\Contracts\Pagination\Paginator;

interface RepositoryContract
{
    /**
     * Adds query criteria.
     *
     * @param  CriteriaContract $criteria
     * @return static
     */
    public function addCriteria(CriteriaContract $criteria): static;

    /**
     * Specifies the query criteria. Removes the previously specified criteria(s).
     *
     * @param  CriteriaContract $criteria
     * @return static
     */
    public function setCriteria(CriteriaContract $criteria): static;

    /**
     * Returns the current query criteria.
     *
     * @return CriteriaContract
     */
    public function getCriteria(): CriteriaContract;

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
     * Adds a function that will be called with the fetched results as its first
     * parameter.
     *
     * @param  callable $callback
     * @return static
     */
    public function addResultCallback(callable $callback): static;

    /**
     * Removes all the result callbacks specified before.
     *
     * @return static
     */
    public function clearResultCallbacks(): static;

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
     * @param  CriteriaContract|null $criteria
     * @return void
     */
    public function loadMissing(object $records, ?CriteriaContract $criteria): void;

    /**
     * Filters records. Simplifies access to the criteria.
     * Should accept the same params as CriteriaContract::where() method.
     *
     * @return static
     * @see \Deluxetech\LaRepo\Contracts\CriteriaContract::where()
     */
    public function where(): static;

    /**
     * Filters records. Simplifies access to the criteria.
     * Should accept the same params as CriteriaContract::orWhere() method.
     *
     * @return static
     * @see \Deluxetech\LaRepo\Contracts\CriteriaContract::orWhere()
     */
    public function orWhere(): static;
}
