<?php

namespace LaravelRepository\Drivers;

use LaravelRepository\Filter;
use LaravelRepository\Pagination;
use LaravelRepository\TextSearch;
use Illuminate\Support\Collection;
use LaravelRepository\FilterGroup;
use LaravelRepository\SearchCriteria;
use Illuminate\Support\LazyCollection;
use LaravelRepository\Filters\IsLikeFilter;
use LaravelRepository\Filters\IsNullFilter;
use LaravelRepository\Enums\FilterGroupMode;
use LaravelRepository\Filters\InRangeFilter;
use LaravelRepository\Filters\IsLowerFilter;
use LaravelRepository\Filters\ContainsFilter;
use Illuminate\Contracts\Pagination\Paginator;
use LaravelRepository\Filters\IsGreaterFilter;
use LaravelRepository\Filters\IsNotLikeFilter;
use LaravelRepository\Filters\IsNotNullFilter;
use LaravelRepository\Filters\IncludedInFilter;
use LaravelRepository\Filters\NotInRangeFilter;
use LaravelRepository\Contracts\SortingContract;
use LaravelRepository\Filters\NotEqualsToFilter;
use LaravelRepository\Contracts\DbDriverContract;
use LaravelRepository\Filters\NotIncludedInFilter;
use LaravelRepository\Filters\DoesNotContainFilter;
use LaravelRepository\Filters\IsLowerOrEqualFilter;
use LaravelRepository\Filters\IsGreaterOrEqualFilter;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class EloquentDriver implements DbDriverContract
{
    /**
     * Initializes the DB driver.
     *
     * @param  object $dbContext
     * @return static
     */
    public static function init(object $dbContext): static
    {
        return new static($dbContext);
    }

    /**
     * Creates an instance of this class.
     *
     * @param  EloquentBuilder $query
     * @return void
     */
    public function __construct(protected EloquentBuilder $query)
    {
        //
    }

    /** @inheritdoc */
    public function distinct(): static
    {
        $this->query->distinct();

        return $this;
    }

    /** @inheritdoc */
    public function select(string ...$attrs): static
    {
        $this->query->select($attrs);

        return $this;
    }

    /** @inheritdoc */
    public function with(string|array $relations, \Closure $callback = null): static
    {
        $this->query->with($relations, $callback);

        return $this;
    }

    /** @inheritdoc */
    public function withCount(array $relations): static
    {
        $this->query->withCount($relations);

        return $this;
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
    public function search(SearchCriteria $query): static
    {
        if ($query->textSearch) {
            $this->applyTextSearch($query->textSearch);
        }

        if ($query->sorting) {
            $this->applySorting($query->sorting);
        }

        if ($query->filters) {
            $this->applyFilters($this->query, $query->filters);
        }

        return $this;
    }

    /** @inheritdoc */
    public function get(): Collection
    {
        QueryHelper::instance()->preventAmbiguousQuery($this->query);

        return $this->query->get();
    }

    /** @inheritdoc */
    public function paginate(Pagination $pagination): Paginator
    {
        QueryHelper::instance()->preventAmbiguousQuery($this->query);

        return $this->query->paginate(
            perPage: $pagination->perPage,
            page: $pagination->page
        );
    }

    /** @inheritdoc */
    public function cursor(): LazyCollection
    {
        QueryHelper::instance()->preventAmbiguousQuery($this->query);

        return $this->query->cursor();
    }

    /** @inheritdoc */
    public function lazy(int $chunkSize = 1000): LazyCollection
    {
        QueryHelper::instance()->preventAmbiguousQuery($this->query);

        return $this->query->lazy($chunkSize);
    }

    /** @inheritdoc */
    public function count(): int
    {
        QueryHelper::instance()->preventAmbiguousQuery($this->query);

        return $this->query->count();
    }

    /** @inheritdoc */
    public function find(int|string $id): mixed
    {
        QueryHelper::instance()->preventAmbiguousQuery($this->query);

        return $this->query->find($id);
    }

    /** @inheritdoc */
    public function first(): mixed
    {
        QueryHelper::instance()->preventAmbiguousQuery($this->query);

        return $this->query->first();
    }

    /** @inheritdoc */
    public function create(array $attributes): object
    {
        $model = $this->query->getModel();

        return $model->create($attributes);
    }

    /** @inheritdoc */
    public function update(object $model, array $attributes): void
    {
        $model->update($attributes);
    }

    /** @inheritdoc */
    public function delete(object $model): void
    {
        $model->delete();
    }

    /**
     * Applies the given text search params on the query.
     *
     * @param  TextSearch $search
     * @return void
     */
    protected function applyTextSearch(TextSearch $search): void
    {
        $attrsCount = count($search->attrs);

        if ($attrsCount === 1) {
            $this->searchForText($this->query, $search->attrs[0], $search->text, false);
        } elseif ($attrsCount > 1) {
            $this->query->where(function ($query) use ($search) {
                foreach ($search->attrs as $i => $attr) {
                    $this->searchForText($query, $attr, $search->text, boolval($i));
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
     * @param  FilterGroup $filters
     * @return void
     */
    protected function applyFilters(
        QueryBuilder|EloquentBuilder $query,
        FilterGroup $filters
    ): void {
        $args = [];
        $method = $filters->orCond ? 'orWhere' : 'where';

        if ($filters->relation) {
            $args[] = $filters->relation;
            $method .= match ($filters->mode) {
                FilterGroupMode::HAS => 'Has',
                FilterGroupMode::DOES_NOT_HAVE => 'DoesntHave',
            };
        }

        $args[] = function ($query) use ($filters) {
            foreach ($filters as $filter) {
                $this->applyFilter($query, $filter);
            }
        };

        $query->{$method}(...$args);
    }

    /**
     * Applies the given filter on the given query.
     *
     * @param  QueryBuilder|EloquentBuilder $query
     * @param  Filter $filter
     * @return void
     */
    protected function applyFilter(
        QueryBuilder|EloquentBuilder $query,
        Filter $filter
    ): void {
        $args = $this->getFilterQueryArgs($filter);
        $method = $this->getFilterQueryMethod($filter);

        if ($filter->orCond) {
            $method = 'or' . ucfirst($method);
        }

        if ($filter->relation) {
            $query->whereHas($filter->relation, fn($q) => $q->{$method}(...$args));
        } else {
            $query->{$method}(...$args);
        }
    }

    /**
     * Returns query arguments for the given filter.
     *
     * @param  Filter $filter
     * @return array
     */
    protected function getFilterQueryArgs(Filter $filter): array
    {
        return match (get_class($filter)) {
            IsLikeFilter::class => [$filter->attr, 'like', '%' . $filter->value . '%'],
            IsNotLikeFilter::class => [$filter->attr, 'not like', '%' . $filter->value . '%'],
            IsGreaterFilter::class => [$filter->attr, '>', $filter->value],
            IsGreaterOrEqualFilter::class => [$filter->attr, '>=', $filter->value],
            IsLowerFilter::class => [$filter->attr, '<', $filter->value],
            IsLowerOrEqualFilter::class => [$filter->attr, '<=', $filter->value],
            NotEqualsToFilter::class => [$filter->attr, '!=', $filter->value],
            IsNullFilter::class => [$filter->attr],
            IsNotNullFilter::class => [$filter->attr],
            default => [$filter->attr, $filter->value],
        };
    }

    /**
     * Returns the corresponding query method for the given filter.
     *
     * @param  Filter $filter
     * @return string
     */
    protected function getFilterQueryMethod(Filter $filter): string
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
     * @param  string $attr
     * @param  string $text
     * @param  bool $orCond
     * @return void
     */
    protected function searchForText(
        QueryBuilder|EloquentBuilder $query,
        string $attr,
        string $text,
        bool $orCond
    ): void {
        $method = $orCond ? 'orWhere' : 'where';

        if (str_contains($attr, '.')) {
            $lastDotPos = strrpos($attr, '.');
            $relation = substr($attr, 0, $lastDotPos);
            $attr = substr($attr, $lastDotPos + 1);
            $method .= 'Has';

            $query->{$method}(
                $relation,
                fn($q) => $q->where($attr, 'like', '%' . $text . '%')
            );
        } else {
            $query->{$method}($attr, 'like', '%' . $text . '%');
        }
    }
}
