<?php

namespace Deluxetech\LaRepo\Eloquent;

use Deluxetech\LaRepo\ClassUtils;
use Illuminate\Support\Collection;
use Deluxetech\LaRepo\Facades\LaRepo;
use Illuminate\Support\LazyCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\Paginator;
use Deluxetech\LaRepo\Contracts\PaginationContract;
use Deluxetech\LaRepo\Contracts\RepositoryContract;

class GenericRepository implements RepositoryContract
{
    use Traits\SupportsSorting;
    use Traits\SupportsFiltration;
    use Traits\SupportsTextSearch;
    use Traits\SupportsQueryContext;
    use Traits\TransformsRelationships;

    /**
     * The current query object.
     *
     * @var Builder
     */
    protected Builder $query;

    /**
     * Callback functions that will be triggered before fetching data.
     *
     * @var array<callable>
     */
    protected array $fetchCallbacks = [];

    /**
     * Callback functions that will be triggered after fetching data on results.
     *
     * @var array<callable>
     */
    protected array $resultCallbacks = [];

    /**
     * Class constructor.
     *
     * @param  string $model
     */
    public function __construct(string $model)
    {
        ClassUtils::checkClassExists($model);
        ClassUtils::checkClassImplements($model, Model::class);

        $this->query = $model::query();
        $this->criteria = LaRepo::newCriteria();
        $this->registerDefaultFilterHandlers();
    }

    public function offset(int $offset): static
    {
        $this->query->skip($offset);

        return $this;
    }

    public function limit(int $count): static
    {
        $this->query->limit($count);

        return $this;
    }

    public function reset(): static
    {
        $this->setCriteria(LaRepo::newCriteria());
        $this->query = $this->query->getModel()->newQuery();

        return $this;
    }

    public function addFetchCallback(callable $callback): static
    {
        $this->fetchCallbacks[] = $callback;

        return $this;
    }

    public function clearFetchCallbacks(): static
    {
        $this->fetchCallbacks = [];

        return $this;
    }

    public function addResultCallback(callable $callback): static
    {
        $this->resultCallbacks[] = $callback;

        return $this;
    }

    public function clearResultCallbacks(): static
    {
        $this->resultCallbacks = [];

        return $this;
    }

    public function get(): Collection
    {
        return $this->fetch('get');
    }

    public function cursor(): LazyCollection
    {
        return $this->fetch('cursor');
    }

    public function lazy(int $chunkSize = 1000): LazyCollection
    {
        return $this->fetch('lazy', $chunkSize);
    }

    public function chunk(int $chunkSize = 1000, callable $callback): void
    {
        $this->fetch('chunk', $chunkSize, $callback);
    }

    public function paginate(PaginationContract $pagination): Paginator
    {
        $page = $pagination->getPage();
        $pageName = $pagination->getPageName();
        $perPage = $pagination->getPerPage();
        $perPageName = $pagination->getPerPageName();

        $result = $this->fetch('paginate', $perPage, ['*'], $pageName, $page);
        $result->appends($perPageName, $perPage);

        return $result;
    }

    public function count(): int
    {
        return $this->fetch('count');
    }

    public function exists(): bool
    {
        return $this->fetch('exists');
    }

    public function find(int|string $id): ?object
    {
        return $this->fetch('find', $id);
    }

    public function first(): ?object
    {
        return $this->fetch('first');
    }

    public function where(): static
    {
        $this->criteria->where(...func_get_args());

        return $this;
    }

    public function orWhere(): static
    {
        $this->criteria->orWhere(...func_get_args());

        return $this;
    }

    /**
     * Fetches data from the current query with the given method.
     *
     * @param  string $method
     * @param  mixed ...$args
     * @return mixed
     */
    protected function fetch(string $method, mixed ...$args): mixed
    {
        $this->prepareQuery();
        $result = $this->query->{$method}(...$args);
        $this->reset();

        foreach ($this->resultCallbacks as $callback) {
            call_user_func($callback, $result);
        }

        return $result;
    }

    /**
     * Prepares the query object. Applies criteria and executes fetch callbacks.
     *
     * @return void
     */
    protected function prepareQuery(): void
    {
        foreach ($this->fetchCallbacks as $callback) {
            call_user_func($callback, $this->criteria);
        }

        if (isset($this->criteria)) {
            $this->applyCriteria($this->query, $this->criteria);
        }

        (new Query($this->query))->preventColumnAmbiguity();
    }

    /**
     * Returns the query object.
     *
     * @return Builder
     */
    protected function getQuery(): Builder
    {
        return $this->query;
    }
}
