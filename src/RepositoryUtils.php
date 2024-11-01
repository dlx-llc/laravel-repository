<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo;

use InvalidArgumentException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Deluxetech\LaRepo\Enums\BooleanOperator;
use Deluxetech\LaRepo\Contracts\FilterContract;
use Illuminate\Pagination\LengthAwarePaginator;
use Deluxetech\LaRepo\Contracts\SortingContract;
use Deluxetech\LaRepo\Contracts\CriteriaContract;
use Deluxetech\LaRepo\Contracts\DataAttrContract;
use Deluxetech\LaRepo\Contracts\DataMapperContract;
use Deluxetech\LaRepo\Contracts\PaginationContract;
use Deluxetech\LaRepo\Contracts\RepositoryContract;
use Deluxetech\LaRepo\Contracts\TextSearchContract;
use Deluxetech\LaRepo\Rules\Validators\CriteriaValidator;
use Deluxetech\LaRepo\Contracts\FiltersCollectionContract;
use Deluxetech\LaRepo\Rules\Validators\PaginationValidator;

/**
 * Contains methods that make it easy to retrieve data from repositories by
 * applying query criteria, pagination and data mapping.
 */
class RepositoryUtils
{
    /**
     * Fetches data collection from the given repository.
     *
     * @return LengthAwarePaginator<int,mixed>|Collection<int,mixed>
     */
    public function getMany(
        RepositoryContract $repository,
        ?CriteriaContract $criteria = null,
        ?PaginationContract $pagination = null,
        ?DataMapperContract $dataMapper = null
    ): LengthAwarePaginator|Collection {
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
     * @return LengthAwarePaginator<int,mixed>|Collection<int,mixed>
     */
    public function getManyWithRequest(
        RepositoryContract $repository,
        ?CriteriaContract $criteria = null,
        ?DataMapperContract $dataMapper = null,
        bool $pageRequired = true,
    ): LengthAwarePaginator|Collection {
        return $this->getMany(
            repository: $repository,
            criteria: $this->getRequestCriteria($criteria),
            pagination: $this->getRequestPagination($pageRequired),
            dataMapper: $dataMapper
        );
    }

    /**
     * Fetches data count from the given repository using request params.
     */
    public function getCountWithRequest(
        RepositoryContract $repository,
        ?DataMapperContract $dataMapper = null,
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
     */
    public function getOneById(
        RepositoryContract $repository,
        int|string $id,
        ?CriteriaContract $criteria = null,
        ?DataMapperContract $dataMapper = null,
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
     */
    public function getFirst(
        RepositoryContract $repository,
        ?CriteriaContract $criteria = null,
        ?DataMapperContract $dataMapper = null,
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
     */
    public function getRequestCriteria(
        ?CriteriaContract $criteria = null,
        ?string $textSearchKey = null,
        ?string $sortingKey = null,
        ?string $filtersKey = null,
    ): CriteriaContract {
        $validator = new CriteriaValidator($textSearchKey, $sortingKey, $filtersKey);
        $validator->validate();

        if (is_null($criteria)) {
            $criteria = App::make(CriteriaContract::class);
        }

        $validator->fillValidated($criteria);

        return $criteria;
    }

    /**
     * Creates a new query criteria object.
     */
    public function newCriteria(): CriteriaContract
    {
        return App::make(CriteriaContract::class);
    }

    /**
     * Creates a new data mapper object.
     */
    public function newDataMapper(): DataMapperContract
    {
        return App::make(DataMapperContract::class);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function newFilter(
        string $attr,
        string $operator,
        mixed $value,
        string $boolean = BooleanOperator::AND
    ): FilterContract {
        $filterClass = FilterRegistry::getClass($operator);

        if (!$filterClass) {
            /** @var string $message */
            $message = __('larepo::exceptions.undefined_repo_filter_operator');

            throw new InvalidArgumentException($message);
        }

        $attr = App::makeWith(DataAttrContract::class, [$attr]);

        return new $filterClass($attr, $operator, $value, $boolean);
    }

    /**
     * Creates a new filters collection object.
     */
    public function newFiltersCollection(
        string $boolean = BooleanOperator::AND,
        FiltersCollectionContract|FilterContract ...$items,
    ): FiltersCollectionContract {
        return App::makeWith(FiltersCollectionContract::class, [$boolean, ...$items]);
    }

    /**
     * Creates a new sorting object.
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
