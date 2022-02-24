<?php

namespace LaravelRepository\Drivers;

use LaravelRepository\Pagination;
use Illuminate\Support\Collection;
use LaravelRepository\SearchContext;
use Illuminate\Support\LazyCollection;
use Illuminate\Contracts\Pagination\Paginator;

interface DbDriverContract
{
    /**
     * Instantiates a DB driver.
     *
     * @param  mixed $dbContext
     * @return static
     */
    public static function init(mixed $dbContext): static;

    /**
     * Sets the data attributes that should be fetched.
     *
     * @param  string ...$attrs
     * @return static
     */
    public function select(string ...$attrs): static;

    /**
     * Specifies that duplicate results should be excluded.
     *
     * @return static
     */
    public function distinct(): static;

    /**
     * Sets the relationships that should be eager loaded.
     *
     * @param  string|array $relations
     * @param  \Closure|null $callback
     * @return static
     */
    public function with(string|array $relations, \Closure $callback = null): static;

    /**
     * Sets the relationship counts that should be loaded with data.
     *
     * @param  array $relations
     * @return static
     */
    public function withCount(array $relations): static;

    /**
     * Sets a limit for the number of results.
     *
     * @param  int $count
     * @return static
     */
    public function limit(int $count): static;

    /**
     * Applies the search context.
     *
     * @param  SearchContext $query
     * @return static
     */
    public function search(SearchContext $query): static;

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
     * Returns the number of query results.
     *
     * @return int
     */
    public function count(): int;

    /**
     * Fetches a single result from the query by ID.
     *
     * @param  int|string $id
     * @return mixed
     */
    public function find(int|string $id): mixed;

    /**
     * Fetches the first result from the query.
     *
     * @return mixed
     */
    public function first(): mixed;

    /**
     * Creates a new data model and returns the instance.
     *
     * @param  array $attributes
     * @return mixed
     */
    public function create(array $attributes): mixed;

    /**
     * Updates the given data model.
     *
     * @param  mixed $model
     * @param  array $attributes
     * @return void
     */
    public function update(mixed $model, array $attributes): void;

    /**
     * Deletes the given data model.
     *
     * @param  mixed $model
     * @return void
     */
    public function delete(mixed $model): void;
}
