<?php

namespace Deluxetech\LaRepo;

use Deluxetech\LaRepo\Contracts\CriteriaContract;
use Deluxetech\LaRepo\Contracts\DataAttrContract;
use Deluxetech\LaRepo\Contracts\DataMapperContract;
use Deluxetech\LaRepo\Contracts\FilterContract;
use Deluxetech\LaRepo\Contracts\FiltersCollectionContract;
use Deluxetech\LaRepo\Contracts\PaginationContract;
use Deluxetech\LaRepo\Contracts\RepositoryContract;
use Deluxetech\LaRepo\Contracts\RequestQueryContract;
use Deluxetech\LaRepo\Contracts\SortingContract;
use Deluxetech\LaRepo\Contracts\TextSearchContract;
use Deluxetech\LaRepo\Enums\BooleanOperator;
use Deluxetech\LaRepo\Rules\Validators\CriteriaValidator;
use Deluxetech\LaRepo\Rules\Validators\PaginationValidator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

/**
 * Contains methods that make it easy to retrieve data from repositories by
 * applying query criteria, pagination and data mapping.
 */
class RepositoryUtils
{
    /**
     * Fetches data collection from the given repository.
     *
     * @param  RepositoryContract $repository
     * @param  CriteriaContract|null $criteria
     * @param  PaginationContract|null $pagination
     * @param  DataMapperContract|null $dataMapper
     * @return Paginator|Collection
     */
    public function getMany(
        RepositoryContract $repository,
        ?CriteriaContract $criteria = null,
        ?PaginationContract $pagination = null,
        ?DataMapperContract $dataMapper = null
    ): Paginator|Collection {
        if ($criteria) {
            if ($dataMapper) {
                $dataMapper->applyOnCriteria($criteria);
            }

            $repository->addCriteria($criteria);
        }

        return !is_null($pagination)
            ? $repository->paginate($pagination)
            : $repository->get();
    }

    /**
     * Fetches data collection from the given repository using request params.
     *
     * @param  RepositoryContract $repository
     * @param  CriteriaContract|null $criteria
     * @param  DataMapperContract|null $dataMapper
     * @param  bool $pageRequired  TRUE by default.
     * @return Paginator|Collection
     */
    public function getManyWithRequest(
        RepositoryContract $repository,
        ?CriteriaContract $criteria = null,
        ?DataMapperContract $dataMapper = null,
        bool $pageRequired = true
    ): Paginator|Collection {
        return $this->getMany(
            repository: $repository,
            criteria: $this->getRequestCriteria($criteria),
            pagination: $this->getRequestPagination($pageRequired),
            dataMapper: $dataMapper
        );
    }

    /**
     * Fetches data count from the given repository using request params.
     *
     * @param  RepositoryContract $repository
     * @param  DataMapperContract|null $dataMapper
     * @return int
     */
    public function getCountWithRequest(
        RepositoryContract $repository,
        ?DataMapperContract $dataMapper = null
    ): int {
        if ($criteria = $this->getRequestCriteria()) {
            if ($dataMapper) {
                $dataMapper->applyOnCriteria($criteria);
            }

            $repository->addCriteria($criteria);
        }

        return $repository->count();
    }

    /**
     * Fetches a single data model from the given repository by ID.
     *
     * @param  RepositoryContract $repository
     * @param  int|string $id
     * @param  CriteriaContract|null $criteria
     * @param  DataMapperContract|null $dataMapper
     * @return object|null
     */
    public function getOneById(
        RepositoryContract $repository,
        int|string $id,
        ?CriteriaContract $criteria = null,
        ?DataMapperContract $dataMapper = null
    ): ?object {
        if ($criteria) {
            if ($dataMapper) {
                $dataMapper->applyOnCriteria($criteria);
            }

            $repository->addCriteria($criteria);
        }

        return $repository->find($id);
    }

    /**
     * Fetches a single data model from the given repository.
     *
     * @param  RepositoryContract $repository
     * @param  CriteriaContract|null $criteria
     * @param  DataMapperContract|null $dataMapper
     * @return object|null
     */
    public function getFirst(
        RepositoryContract $repository,
        ?CriteriaContract $criteria = null,
        ?DataMapperContract $dataMapper = null
    ): ?object {
        if ($criteria) {
            if ($dataMapper) {
                $dataMapper->applyOnCriteria($criteria);
            }

            $repository->addCriteria($criteria);
        }

        return $repository->first();
    }

    /**
     * Creates a new pagination object using the parameters of the request.
     *
     * @param  bool $require
     * @param  int|null $perPageMax
     * @param  string|null $pageKey
     * @param  string|null $perPageKey
     * @return PaginationContract|null
     */
    public function getRequestPagination(
        bool $require = true,
        ?int $perPageMax = null,
        ?string $pageKey = null,
        ?string $perPageKey = null
    ): ?PaginationContract {
        $validator = new PaginationValidator($pageKey, $perPageKey);
        $validator->validate($require, $perPageMax);

        return $validator->createFromValidated();
    }

    /**
     * Fetches criteria parameters from the request and creates a new criteria
     * object or fills the given one.
     *
     * @param  CriteriaContract|null $criteria
     * @param  string|null $textSearchKey
     * @param  string|null $sortingKey
     * @param  string|null $filtersKey
     * @return CriteriaContract
     */
    public function getRequestCriteria(
        ?CriteriaContract $criteria = null,
        ?string $textSearchKey = null,
        ?string $sortingKey = null,
        ?string $filtersKey = null
    ): CriteriaContract {
        $validator = new CriteriaValidator($textSearchKey, $sortingKey, $filtersKey);
        $validator->validate();

        if (is_null($criteria)) {
            $criteria = App::make(CriteriaContract::class);
        }

        $validator->fillValidated($criteria);

        return $criteria;
    }

    public function getRequestQuery(
        ?CriteriaContract $criteria = null,
        ?DataMapperContract $dataMapper = null,
        bool $requirePagination = true,
    ): RequestQueryContract {
        $query = App::make(RequestQueryContract::class);

        $pagination = $this->getRequestPagination($requirePagination);
        $query->setPagination($pagination);

        $requestCriteria = $this->getRequestCriteria();
        $query->addCriteria($requestCriteria);

        if ($criteria) {
            $query->addCriteria($criteria);
        }

        if ($dataMapper) {
            $query->applyDataMapper($dataMapper);
        }

        return $query;
    }

    /**
     * Creates a new query criteria object.
     *
     * @return CriteriaContract
     */
    public function newCriteria(): CriteriaContract
    {
        return App::make(CriteriaContract::class);
    }

    /**
     * Creates a new data mapper object.
     *
     * @return DataMapperContract
     */
    public function newDataMapper(): DataMapperContract
    {
        return App::make(DataMapperContract::class);
    }

    /**
     * Creates a new filter object.
     *
     * @param  string $attr
     * @param  string $operator
     * @param  mixed $value
     * @param  string $boolean
     * @return FilterContract
     * @throws \Exception
     */
    public function newFilter(
        string $attr,
        string $operator,
        mixed $value,
        string $boolean = BooleanOperator::AND
    ): FilterContract {
        $filterClass = FilterRegistry::getClass($operator);

        if (!$filterClass) {
            throw new \Exception(__('larepo::exceptions.undefined_repo_filter_operator'));
        }

        $attr = App::makeWith(DataAttrContract::class, [$attr]);

        return new $filterClass($attr, $operator, $value, $boolean);
    }

    /**
     * Creates a new filters collection object.
     *
     * @param  string $boolean
     * @param  FiltersCollectionContract|FilterContract ...$items
     * @return FiltersCollectionContract
     */
    public function newFiltersCollection(
        string $boolean = BooleanOperator::AND,
        FiltersCollectionContract|FilterContract ...$items
    ): FiltersCollectionContract {
        return App::makeWith(FiltersCollectionContract::class, [$boolean, ...$items]);
    }

    /**
     * Creates a new sorting object.
     *
     * @param  DataAttrContract|string $attr
     * @param  string $dir
     * @return SortingContract
     */
    public function newSorting(DataAttrContract|string $attr, string $dir): SortingContract
    {
        if (is_string($attr)) {
            $attr = App::makeWith(DataAttrContract::class, [$attr]);
        }

        return App::makeWith(SortingContract::class, [
            'attr' => $attr,
            'dir' => $dir,
        ]);
    }

    /**
     * Creates a new text search object.
     *
     * @param  string $text
     * @param  DataAttrContract|string ...$attrs
     * @return TextSearchContract
     */
    public function newTextSearch(string $text, DataAttrContract|string ...$attrs): TextSearchContract
    {
        foreach ($attrs as $i => $attr) {
            if (is_string($attr)) {
                $attrs[$i] = App::makeWith(DataAttrContract::class, [$attr]);
            }
        }

        return App::makeWith(TextSearchContract::class, [$text, ...$attrs]);
    }
}
