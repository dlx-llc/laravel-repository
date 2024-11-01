<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Eloquent;

use Closure;
use Deluxetech\LaRepo\ClassUtils;
use Illuminate\Support\Collection;
use Deluxetech\LaRepo\Facades\LaRepo;
use Illuminate\Support\LazyCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Deluxetech\LaRepo\Contracts\CriteriaContract;
use Deluxetech\LaRepo\Contracts\PaginationContract;
use Deluxetech\LaRepo\Contracts\RepositoryContract;
use Deluxetech\LaRepo\Eloquent\Criteria\CriteriaProcessor;

/**
 * @template-implements RepositoryContract<Model>
 */
class GenericRepository implements RepositoryContract
{
    /**
     * The current query object.
     */
    protected Builder $query;

    /**
     * The current query criteria.
     */
    protected CriteriaContract $criteria;

    protected CriteriaProcessor $criteriaProcessor;

    /**
     * Callback functions that will be triggered before fetching data.
     *
     * @var array<Closure(CriteriaContract):void>
     */
    protected array $fetchCallbacks = [];

    /**
     * Callback functions that will be triggered after fetching data on results.
     *
     * @var array<Closure(iterable<TResult>):void>
     */
    protected array $resultCallbacks = [];

    /**
     * @param class-string<Model> $model
     */
    public function __construct(string $model)
    {
        ClassUtils::checkClassExists($model);
        ClassUtils::checkClassImplements($model, Model::class);

        $this->query = $model::query();
        $this->criteria = LaRepo::newCriteria();
        $this->criteriaProcessor = new CriteriaProcessor($model);
    }

    public function addCriteria(CriteriaContract $criteria): static
    {
        $this->criteria->merge($criteria);

        return $this;
    }

    public function setCriteria(CriteriaContract $criteria): static
    {
        $this->criteria = $criteria;

        return $this;
    }

    public function getCriteria(): CriteriaContract
    {
        return $this->criteria;
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

    public function addFetchCallback(Closure $callback): static
    {
        $this->fetchCallbacks[] = $callback;

        return $this;
    }

    public function clearFetchCallbacks(): static
    {
        $this->fetchCallbacks = [];

        return $this;
    }

    public function addResultCallback(Closure $callback): static
    {
        $this->resultCallbacks[] = $callback;

        return $this;
    }

    public function clearResultCallbacks(): static
    {
        $this->resultCallbacks = [];

        return $this;
    }

    public function loadMissing(iterable $records, ?CriteriaContract $criteria): void
    {
        if (!$criteria) {
            return;
        } elseif (!is_a($records, Collection::class)) {
            $records = Collection::make($records);
        }

        $this->criteriaProcessor->loadMissing($records, $criteria);
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

    public function chunk(int $chunkSize, Closure $callback): void
    {
        $this->fetch('chunk', $chunkSize, $callback);
    }

    public function paginate(PaginationContract $pagination): LengthAwarePaginator
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
     * Resets the query object and applies result callbacks.
     */
    protected function fetch(string $method, mixed ...$args): mixed
    {
        $this->prepareQuery();
        $result = $this->query->{$method}(...$args);
        $this->reset();

        foreach ($this->resultCallbacks as $callback) {
            $callback($result);
        }

        return $result;
    }

    /**
     * Prepares the query object. Applies criteria and executes fetch callbacks.
     */
    protected function prepareQuery(): void
    {
        foreach ($this->fetchCallbacks as $callback) {
            $callback($this->criteria);
        }

        if (isset($this->criteria)) {
            $this->criteriaProcessor->processCriteria($this->query, $this->criteria);
        }

        (new Query($this->query))->preventColumnAmbiguity();
    }

    protected function getQuery(): Builder
    {
        return $this->query;
    }
}
