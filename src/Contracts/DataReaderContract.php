<?php

namespace LaravelRepository\Contracts;

use LaravelRepository\Pagination;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Illuminate\Contracts\Pagination\Paginator;

interface DataReaderContract
{
    /**
     * Specifies data attributes that should be fetched.
     *
     * @param  string ...$attrs
     * @return static
     */
    public function select(string ...$attrs): static;

    /**
     * Specifies relationships that should be eager loaded with result(s).
     *
     * @param  string|array $relations
     * @param  \Closure|null $callback
     * @return static
     */
    public function with(string|array $relations, \Closure $callback = null): static;

    /**
     * Specifies relationship counts that should be loaded with result(s).
     *
     * @param  array $relations
     * @return static
     */
    public function withCount(array $relations): static;

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
     * @param  SearchCriteriaContract $query
     * @return static
     */
    public function search(SearchCriteriaContract $query): static;

    /**
     * Fetches query results.
     *
     * @return Collection
     */
    public function get(): Collection;

    /**
     * Fetches paginated query results.
     *
     * @param  Pagination $pagination
     * @return Paginator
     */
    public function paginate(Pagination $pagination): Paginator;

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
     * @return mixed
     */
    public function find(int|string $id): mixed;

    /**
     * Fetches the first matching record.
     *
     * @return mixed
     */
    public function first(): mixed;
}
