<?php

namespace Deluxetech\LaRepo\Strategies;

use Illuminate\Support\Collection;
use Deluxetech\LaRepo\RepositoryUtils;
use Illuminate\Support\LazyCollection;
use Illuminate\Database\Eloquent\Model;
use Deluxetech\LaRepo\Enums\FilterOperator;
use Deluxetech\LaRepo\Filters\IsLikeFilter;
use Deluxetech\LaRepo\Filters\IsNullFilter;
use Deluxetech\LaRepo\Filters\InRangeFilter;
use Deluxetech\LaRepo\Filters\IsLowerFilter;
use Deluxetech\LaRepo\Filters\ContainsFilter;
use Deluxetech\LaRepo\Filters\IsGreaterFilter;
use Deluxetech\LaRepo\Filters\IsNotLikeFilter;
use Deluxetech\LaRepo\Filters\IsNotNullFilter;
use Illuminate\Contracts\Pagination\Paginator;
use Deluxetech\LaRepo\Contracts\FilterContract;
use Deluxetech\LaRepo\Filters\IncludedInFilter;
use Deluxetech\LaRepo\Filters\NotInRangeFilter;
use Deluxetech\LaRepo\Contracts\SortingContract;
use Deluxetech\LaRepo\Filters\NotEqualsToFilter;
use Deluxetech\LaRepo\Contracts\DataAttrContract;
use Deluxetech\LaRepo\Filters\NotIncludedInFilter;
use Deluxetech\LaRepo\Contracts\PaginationContract;
use Deluxetech\LaRepo\Contracts\TextSearchContract;
use Deluxetech\LaRepo\Filters\DoesNotContainFilter;
use Deluxetech\LaRepo\Filters\IsLowerOrEqualFilter;
use Deluxetech\LaRepo\Filters\RelationExistsFilter;
use Deluxetech\LaRepo\Filters\IsGreaterOrEqualFilter;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Deluxetech\LaRepo\Contracts\SearchCriteriaContract;
use Deluxetech\LaRepo\Filters\RelationDoesNotExistFilter;
use Deluxetech\LaRepo\Contracts\FiltersCollectionContract;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

/**
 * Implements methods from DataReaderContract interface.
 */
trait EloquentQueryStrategy
{
    /**
     * The current query object.
     *
     * @var EloquentBuilder
     */
    protected EloquentBuilder $query;

    /**
     * Filter handlers.
     *
     * @var array
     */
    protected array $filterHandlers = [
        RelationExistsFilter::class => 'applyRelationExistsFilter',
        RelationDoesNotExistFilter::class => 'applyRelationDoesNotExistFilter',
    ];

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
    public function search(SearchCriteriaContract $criteria): static
    {
        if ($textSearch = $criteria->getTextSearch()) {
            $this->applyTextSearch($textSearch);
        }

        if ($sorting = $criteria->getSorting()) {
            $this->applySorting($sorting);
        }

        if ($filters = $criteria->getFilters()) {
            $this->applyFilters($this->query, $filters);
        }

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
    public function find(int|string $id): object
    {
        return $this->fetch('find', $id);
    }

    /** @inheritdoc */
    public function first(): object
    {
        return $this->fetch('first');
    }

    /**
     * Initializes the query object from the given eloquent model.
     *
     * @param  string $model
     * @return void
     */
    protected function initQuery(string $model): void
    {
        RepositoryUtils::checkClassExists($model);
        RepositoryUtils::checkClassImplements($model, Model::class);

        $this->query = $model::query();
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
     * Applies the given text search params on the query.
     *
     * @param  TextSearchContract $search
     * @return void
     */
    protected function applyTextSearch(TextSearchContract $search): void
    {
        $attrs = $search->getAttrs();
        $attrsCount = count($attrs);

        if ($attrsCount === 1) {
            $this->searchForText($this->query, $attrs[0], $search->getText(), false);
        } elseif ($attrsCount > 1) {
            $this->query->where(function ($query) use ($search, $attrs) {
                foreach ($attrs as $i => $attr) {
                    $this->searchForText($query, $attr, $search->getText(), boolval($i));
                }
            });
        }
    }

    /**
     * Applies the given sorting params on the query.
     *
     * @param  SortingContract $sorting
     * @return void
     */
    protected function applySorting(SortingContract $sorting): void
    {
        $attr = $sorting->getAttr();

        if ($relation = $attr->getRelation()) {
            $this->query->leftJoinRelation($relation)->distinct();
            $lastJoin = last($this->query->getQuery()->joins);
            $lastJoinTable = QueryHelper::instance()->tableName($lastJoin);
            $attr = $lastJoinTable . '.' . $attr->getName();
            $this->query->orderBy($attr, $sorting->getDir());
        } else {
            $this->query->orderBy($attr->getName(), $sorting->getDir());
        }
    }

    /**
     * Applies the given filters on the query.
     *
     * @param  FiltersCollectionContract $filters
     * @return void
     */
    protected function applyFilters(
        QueryBuilder|EloquentBuilder $query,
        FiltersCollectionContract $filters
    ): void {
        $method = match ($filters->getOperator()) {
            FilterOperator::AND => 'where',
            FilterOperator::OR => 'orWhere',
        };

        $query->{$method}(function ($query) use ($filters) {
            foreach ($filters as $filter) {
                if (is_a($filter, FiltersCollectionContract::class)) {
                    $this->applyFilters($query, $filter);
                } else {
                    $this->applyFilter($query, $filter);
                }
            }
        });
    }

    /**
     * Applies the given filter on the given query.
     *
     * @param  QueryBuilder|EloquentBuilder $query
     * @param  FilterContract $filter
     * @return void
     */
    protected function applyFilter(
        QueryBuilder|EloquentBuilder $query,
        FilterContract $filter
    ): void {
        if ($handler = $this->filterHandlers[get_class($filter)] ?? false) {
            $this->{$handler}($query, $filter);

            return;
        }

        $args = $this->getFilterQueryArgs($filter);
        $method = $this->getFilterQueryMethod($filter);

        if ($filter->getOperator() === FilterOperator::OR) {
            $method = 'or' . ucfirst($method);
        }

        $attr = $filter->getAttr();
        $relation = $attr->getRelation();

        if ($relation) {
            $query->whereHas($relation, fn($q) => $q->{$method}(...$args));
        } else {
            $query->{$method}(...$args);
        }
    }

    /**
     * Applies relation exists filter.
     *
     * @param  QueryBuilder|EloquentBuilder $query
     * @param  FilterContract $filter
     * @return void
     */
    protected function applyRelationExistsFilter(
        QueryBuilder|EloquentBuilder $query,
        FilterContract $filter
    ): void {
        $relation = $filter->getAttr()->getNameWithRelation();
        $query->whereHas($relation, function ($query) use ($filter) {
            $this->applyFilters($query, $filter->value);
        });
    }

    /**
     * Applies relation does not exist filter.
     *
     * @param  QueryBuilder|EloquentBuilder $query
     * @param  FilterContract $filter
     * @return void
     */
    protected function applyRelationDoesNotExistFilter(
        QueryBuilder|EloquentBuilder $query,
        FilterContract $filter
    ): void {
        $relation = $filter->getAttr()->getNameWithRelation();
        $query->whereDoesntHave($relation, function ($query) use ($filter) {
            $this->applyFilters($query, $filter->value);
        });
    }

    /**
     * Returns query arguments for the given filter.
     *
     * @param  FilterContract $filter
     * @return array
     */
    protected function getFilterQueryArgs(FilterContract $filter): array
    {
        $attr = $filter->getAttr()->getName();

        return match (get_class($filter)) {
            IsLikeFilter::class => [$attr, 'like', '%' . $filter->getValue() . '%'],
            IsNotLikeFilter::class => [$attr, 'not like', '%' . $filter->getValue() . '%'],
            IsGreaterFilter::class => [$attr, '>', $filter->getValue()],
            IsGreaterOrEqualFilter::class => [$attr, '>=', $filter->getValue()],
            IsLowerFilter::class => [$attr, '<', $filter->getValue()],
            IsLowerOrEqualFilter::class => [$attr, '<=', $filter->getValue()],
            NotEqualsToFilter::class => [$attr, '!=', $filter->getValue()],
            IsNullFilter::class => [$attr],
            IsNotNullFilter::class => [$attr],
            default => [$attr, $filter->getValue()],
        };
    }

    /**
     * Returns the corresponding query method for the given filter.
     *
     * @param  FilterContract $filter
     * @return string
     */
    protected function getFilterQueryMethod(FilterContract $filter): string
    {
        return match (get_class($filter)) {
            IncludedInFilter::class => 'whereIn',
            NotIncludedInFilter::class => 'whereNotIn',
            InRangeFilter::class => 'whereBetween',
            NotInRangeFilter::class => 'whereNotBetween',
            IsNullFilter::class => 'whereNull',
            IsNotNullFilter::class => 'whereNotNull',
            ContainsFilter::class => 'whereJsonContains',
            DoesNotContainFilter::class => 'whereJsonDoesntContain',
            default => 'where',
        };
    }

    /**
     * Searches for the given text in the given query's data attribute.
     *
     * @param  QueryBuilder|EloquentBuilder $query
     * @param  DataAttrContract $attr
     * @param  string $text
     * @param  bool $orCond
     * @return void
     */
    protected function searchForText(
        QueryBuilder|EloquentBuilder $query,
        DataAttrContract $attr,
        string $text,
        bool $orCond
    ): void {
        $field = $attr->getName();
        $relation = $attr->getRelation();
        $method = $orCond ? 'orWhere' : 'where';
        $args = [$field, 'like', '%' . $text . '%'];

        if ($relation) {
            $method .= 'Has';
            $query->{$method}($relation, fn($q) => $q->where(...$args));
        } else {
            $query->{$method}(...$args);
        }
    }
}
