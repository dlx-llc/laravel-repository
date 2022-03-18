<?php

namespace Deluxetech\LaRepo\Eloquent;

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
use Deluxetech\LaRepo\Contracts\DataMapperContract;
use Deluxetech\LaRepo\Contracts\DataReaderContract;
use Deluxetech\LaRepo\Contracts\PaginationContract;
use Deluxetech\LaRepo\Contracts\TextSearchContract;
use Deluxetech\LaRepo\Filters\DoesNotContainFilter;
use Deluxetech\LaRepo\Filters\IsLowerOrEqualFilter;
use Deluxetech\LaRepo\Filters\RelationExistsFilter;
use Deluxetech\LaRepo\Contracts\LoadContextContract;
use Deluxetech\LaRepo\Filters\IsGreaterOrEqualFilter;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Deluxetech\LaRepo\Contracts\SearchCriteriaContract;
use Deluxetech\LaRepo\Filters\RelationDoesNotExistFilter;
use Deluxetech\LaRepo\Contracts\FiltersCollectionContract;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

abstract class ReadonlyRepository implements DataReaderContract
{
    /**
     * The current query object.
     *
     * @var EloquentBuilder
     */
    protected EloquentBuilder $query;

    /**
     * The data mapper.
     *
     * @var DataMapperContract|null
     */
    protected ?DataMapperContract $dataMapper = null;

    /**
     * Filter handlers.
     *
     * @var array
     */
    protected array $filterHandlers = [
        RelationExistsFilter::class => 'applyRelationExistsFilter',
        RelationDoesNotExistFilter::class => 'applyRelationDoesNotExistFilter',
    ];

    /**
     * Relation resolvers map.
     *
     * @var array[string => callable]
     */
    protected array $relationResolvers = [];

    /**
     * Relation count resolvers map.
     *
     * @var array[string => callable]
     */
    protected array $relationCountResolvers = [];

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
    public function setDataMapper(?DataMapperContract $dataMapper): static
    {
        $this->dataMapper = $dataMapper;

        return $this;
    }

    /** @inheritdoc */
    public function setLoadContext(LoadContextContract $context): static
    {
        $this->applyLoadContext($this->query, $context);

        return $this;
    }

    /** @inheritdoc */
    public function loadMissing(object $record, LoadContextContract $context): void
    {
        $this->loadMissingAttrs($record, $context->getAttributes());
        $this->loadMissingRelations($record, $context->getRelations());
        $this->loadMissingRelationCounts($record, $context->getRelationCounts());
    }

    /** @inheritdoc */
    public function search(SearchCriteriaContract $criteria): static
    {
        if ($this->dataMapper) {
            $this->dataMapper->applyOnSearchCriteria($criteria);
        }

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
            $this->applyFilters($query, $filter->getValue());
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
            $this->applyFilters($query, $filter->getValue());
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

    /**
     * Specifies the relation resolver callable.
     *
     * @param  string $relation
     * @param  callable $resolver
     * @return void
     */
    protected function setRelationResolver(string $relation, callable $resolver): void
    {
        $this->relationResolvers[$relation] = $resolver;
    }

    /**
     * Returns the relation resolver callable.
     *
     * @param  string $relation
     * @return callable
     */
    protected function getRelationResolver(string $relation): callable
    {
        return $this->relationResolvers[$relation] ?? [$this, 'loadMissingRelation'];
    }

    /**
     * Specifies the relation count resolver callable.
     *
     * @param  string $relation
     * @param  callable $resolver  Function that returns the count.
     * @return void
     */
    protected function setRelationCountResolver(string $relation, callable $resolver): void
    {
        $this->relationCountResolvers[$relation] = $resolver;
    }

    /**
     * Returns the relation count resolver callable.
     *
     * @param  string $relation
     * @return callable|null
     */
    protected function getRelationCountResolver(string $relation): ?callable
    {
        return $this->relationCountResolvers[$relation] ?? null;
    }

    /**
     * Loads missing attributes on the given model.
     *
     * @param  Model $record
     * @param  array $attrs
     * @return void
     */
    protected function loadMissingAttrs(Model $record, array $attrs): void
    {
        if (!$attrs) {
            return;
        }

        $id = $record->getKey();

        if (!$id) {
            return;
        }

        $missing = [];
        $loaded = $record->getAttributes();

        foreach ($attrs as $attr) {
            if (!isset($record->{$attr}) && !array_key_exists($attr, $loaded)) {
                $missing[] = $attr;
            }
        }

        if (!$missing) {
            return;
        }

        $missingAttrsRecord = $this->query->select($missing)->find($id);

        if (!$missingAttrsRecord) {
            return;
        }

        $missingAttrs = $missingAttrsRecord->getAttributes();

        foreach ($missingAttrs as $attr => $value) {
            $record->setAttribute($attr, $value);
        }
    }

    /**
     * Loads missing relations on the given model.
     *
     * @param  Model $record
     * @param  array $relations
     * @return void
     */
    protected function loadMissingRelations(Model $record, array $relations): void
    {
        if (!$relations) {
            return;
        }

        foreach ($relations as $key => $value) {
            $relation = is_int($key) ? $value : $key;

            $resolver = is_a($record, $this->getModel())
                ? $this->getRelationResolver($relation)
                : [$this, 'loadMissingRelation'];

            $loadContext = is_object($value) && is_subclass_of($value, LoadContextContract::class)
                ? $value
                : null;

            call_user_func_array($resolver, [$record, $relation, $loadContext]);
        }
    }

    /**
     * Loads the missing relation.
     *
     * @param  Model $record
     * @param  string $relation
     * @param  LoadContextContract|null $loadContext
     * @return void
     */
    protected function loadMissingRelation(
        Model $record,
        string $relation,
        ?LoadContextContract $loadContext = null
    ): void {
        if (!$loadContext) {
            $record->loadMissing($relation);
        } elseif ($record->relationLoaded($relation)) {
            $this->loadMissing($record->{$relation}, $loadContext);
        } else {
            $query = $record->{$relation}();

            if ($attrs = $loadContext->getAttributes()) {
                $query->select($attrs);
            }

            if ($counts = $loadContext->getRelationCounts()) {
                $counts = array_map(fn($r) => "$r as {$r}Count", $counts);
                $query->withCount($counts);
            }

            $relationRecord = $query->getResults();
            $record->setRelation($relation, $relationRecord);
            $subRelations = $loadContext->getRelations();

            if ($relationRecord && $subRelations) {
                $this->loadMissingRelations($relationRecord, $subRelations);
            }
        }
    }

    /**
     * Loads missing relation counts on the given model.
     *
     * @param  Model $record
     * @param  array $counts
     * @return void
     */
    protected function loadMissingRelationCounts(Model $record, array $counts): void
    {
        if (!$counts) {
            return;
        }

        $missing = [];

        foreach ($counts as $relation) {
            $countAttr = $relation . 'Count';
            $resolver = is_a($record, $this->getModel())
                ? $this->getRelationCountResolver($relation)
                : null;

            if ($resolver) {
                $count = call_user_func_array($resolver, [$record, $relation]);
                $record->{$countAttr} = is_int($count) ? $count : 0;
            } elseif (!isset($record->{$countAttr})) {
                $missing[] = "{$relation} as {$countAttr}";
            }
        }

        if (!$missing) {
            return;
        }

        $record->loadCount($missing);
    }

    /**
     * Recursively loads the required relations.
     *
     * @param  object $query
     * @param  LoadContextContract $context
     * @return void
     */
    protected function applyLoadContext(object $query, LoadContextContract $context): void
    {
        if ($attrs = $context->getAttributes()) {
            $query->select($attrs);
        }

        foreach ($context->getRelations() as $key => $value) {
            if (is_int($key)) {
                $query->with($value);
            } elseif (is_string($key)) {
                if (is_subclass_of($value, LoadContextContract::class)) {
                    $query->with($key, function ($query) use ($value) {
                        $this->applyLoadContext($query, $value);
                    });
                } else {
                    $query->with($key);
                }
            }
        }

        if ($counts = $context->getRelationCounts()) {
            $counts = array_map(fn($r) => "$r as {$r}Count", $counts);
            $query->withCount($counts);
        }
    }
}
