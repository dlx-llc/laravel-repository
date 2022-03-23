<?php

namespace Deluxetech\LaRepo\Eloquent;

use Illuminate\Support\Collection;
use Deluxetech\LaRepo\RepositoryUtils;
use Illuminate\Support\LazyCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\Paginator;
use Deluxetech\LaRepo\Contracts\CriteriaContract;
use Deluxetech\LaRepo\Contracts\DataReaderContract;
use Deluxetech\LaRepo\Contracts\PaginationContract;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

abstract class ReadonlyRepository implements DataReaderContract
{
    use Traits\SupportsSorting;
    use Traits\SupportsFiltration;
    use Traits\SupportsTextSearch;
    use Traits\SupportsLoadContext;

    /**
     * The current query object.
     *
     * @var Builder
     */
    protected Builder $query;

    /**
     * Returns the eloquent model class name.
     *
     * @return string
     */
    abstract public function getModel(): string;

    /**
     * Class constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $model = $this->getModel();
        RepositoryUtils::checkClassExists($model);
        RepositoryUtils::checkClassImplements($model, Model::class);

        $this->query = $model::query();
        $this->registerDefaultFilterHandlers();
    }

    /** @inheritdoc */
    public function offset(int $offset): static
    {
        $this->query->skip($offset);

        return $this;
    }

    /** @inheritdoc */
    public function limit(int $count): static
    {
        $this->query->limit($count);

        return $this;
    }

    /** @inheritdoc */
    public function addCriteria(CriteriaContract $criteria): static
    {
        $this->applyCriteria($this->query, $criteria);

        return $this;
    }

    /** @inheritdoc */
    public function reset(): static
    {
        $this->query = $this->query->getModel()->newQuery();

        return $this;
    }

    /** @inheritdoc */
    public function get(): Collection
    {
        return $this->fetch('get');
    }

    /** @inheritdoc */
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

    /** @inheritdoc */
    public function cursor(): LazyCollection
    {
        return $this->fetch('cursor');
    }

    /** @inheritdoc */
    public function lazy(int $chunkSize = 1000): LazyCollection
    {
        return $this->fetch('lazy', $chunkSize);
    }

    /** @inheritdoc */
    public function count(): int
    {
        return $this->fetch('count');
    }

    /** @inheritdoc */
    public function exists(): bool
    {
        return $this->fetch('exists');
    }

    /** @inheritdoc */
    public function find(int|string $id): ?object
    {
        return $this->fetch('find', $id);
    }

    /** @inheritdoc */
    public function first(): ?object
    {
        return $this->fetch('first');
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
        QueryHelper::instance()->preventAmbiguousQuery($this->query);
        $result = $this->query->{$method}(...$args);
        $this->reset();

        return $result;
    }

    /**
     * Applies criteria on the given query.
     *
     * @param  QueryBuilder|EloquentBuilder $query
     * @param  CriteriaContract $criteria
     * @return void
     */
    protected function applyCriteria(
        QueryBuilder|EloquentBuilder $query,
        CriteriaContract $criteria
    ): void {
        if ($context = $criteria->getLoadContext()) {
            $this->applyLoadContext($query, $context);
        }

        if ($textSearch = $criteria->getTextSearch()) {
            $this->applyTextSearch($query, $textSearch);
        }

        if ($sorting = $criteria->getSorting()) {
            $this->applySorting($query, $sorting);
        }

        if ($filters = $criteria->getFilters()) {
            $this->applyFilters($query, $filters);
        }
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
