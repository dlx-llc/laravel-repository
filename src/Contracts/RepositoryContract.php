<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Contracts;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @template TResult of object
 */
interface RepositoryContract
{
    /**
     * Adds query criteria.
     */
    public function addCriteria(CriteriaContract $criteria): static;

    /**
     * Specifies the query criteria. Removes the previously specified criteria(s).
     */
    public function setCriteria(CriteriaContract $criteria): static;

    /**
     * Returns the current query criteria.
     */
    public function getCriteria(): CriteriaContract;

    /**
     * Specifies the number of results that should be skipped.
     */
    public function offset(int $offset): static;

    /**
     * Limits the number of results.
     */
    public function limit(int $count): static;

    /**
     * Resets the query to its initial state.
     */
    public function reset(): static;

    /**
     * Adds a function that will be called before fetching data.
     *
     * @param Closure(CriteriaContract):void $callback
     */
    public function addFetchCallback(Closure $callback): static;

    /**
     * Removes all the fetch callback functions specified before.
     */
    public function clearFetchCallbacks(): static;

    /**
     * Adds a function that will be called with the fetched results as its first
     * parameter.
     *
     * @param Closure(iterable<TResult>):void $callback
     */
    public function addResultCallback(Closure $callback): static;

    /**
     * Removes all the result callback functions specified before.
     */
    public function clearResultCallbacks(): static;

    /**
     * @return Collection<int,TResult>
     */
    public function get(): Collection;

    /**
     * Fetches results via lazy collection.
     *
     * @return LazyCollection<int,TResult>
     */
    public function cursor(): LazyCollection;

    /**
     * Fetches results in chunks via lazy collection.
     *
     * @return LazyCollection<int,TResult>
     */
    public function lazy(int $chunkSize = 1000): LazyCollection;

    /**
     * Fetches results in chunks and passes iterable chunks to the callback.
     *
     * @param Closure(Collection<int,TResult>):void $callback
     */
    public function chunk(int $chunkSize, Closure $callback): void;

    /**
     * Fetches paginated results.
     *
     * @return LengthAwarePaginator<int,TResult>
     */
    public function paginate(PaginationContract $pagination): LengthAwarePaginator;

    /**
     * Returns the number of records matching the specified criteria.
     */
    public function count(): int;

    /**
     * Checks if records matching the specified criteria exist.
     */
    public function exists(): bool;

    /**
     * Fetches a single record by ID.
     *
     * @return ?TResult
     */
    public function find(int|string $id): ?object;

    /**
     * Fetches the first matching record.
     *
     * @return ?TResult
     */
    public function first(): ?object;

    /**
     * Loads missing parameters in accordance with the given criteria.
     *
     * @param iterable<TResult> $records
     */
    public function loadMissing(iterable $records, ?CriteriaContract $criteria): void;

    /**
     * Simplifies access to the criteria where() method.
     * Should accept the same params as CriteriaContract::where().
     *
     * @see CriteriaContract::where()
     */
    public function where(): static;

    /**
     * Simplifies access to the criteria orWhere() method.
     * Should accept the same params as CriteriaContract::orWhere().
     *
     * @see CriteriaContract::orWhere()
     */
    public function orWhere(): static;
}
