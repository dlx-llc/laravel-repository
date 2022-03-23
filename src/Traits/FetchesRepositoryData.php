<?php

namespace Deluxetech\LaRepo\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Deluxetech\LaRepo\PaginationFactory;
use Illuminate\Contracts\Pagination\Paginator;
use Deluxetech\LaRepo\Contracts\CriteriaContract;
use Deluxetech\LaRepo\Contracts\DataMapperContract;
use Deluxetech\LaRepo\Contracts\DataReaderContract;
use Deluxetech\LaRepo\Contracts\PaginationContract;
use Deluxetech\LaRepo\Rules\Validators\CriteriaValidator;

/**
 * Contains methods that make it easy to retrieve data from repositories by
 * applying query criteria, pagination and data mapping.
 */
trait FetchesRepositoryData
{
    /**
     * Fetches data collection from the given repository.
     *
     * @param  DataReaderContract $repository
     * @param  CriteriaContract|null $criteria
     * @param  PaginationContract|null $pagination
     * @param  DataMapperContract|null $dataMapper
     * @return Paginator|Collection
     */
    public function getMany(
        DataReaderContract $repository,
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
     * @param  DataReaderContract $repository
     * @param  CriteriaContract|null $criteria
     * @param  DataMapperContract|null $dataMapper
     * @param  bool $pageRequired  TRUE by default.
     * @return Paginator|Collection
     */
    public function getManyWithRequest(
        DataReaderContract $repository,
        ?CriteriaContract $criteria = null,
        ?DataMapperContract $dataMapper = null,
        bool $pageRequired = true
    ): Paginator|Collection {
        return $this->getMany(
            repository: $repository,
            criteria: $this->getRequestCriteria($criteria),
            pagination: PaginationFactory::createFromRequest(require: $pageRequired),
            dataMapper: $dataMapper
        );
    }

    /**
     * Fetches data count from the given repository using request params.
     *
     * @param  DataReaderContract $repository
     * @param  DataMapperContract|null $dataMapper
     * @return int
     */
    public function getCountWithRequest(
        DataReaderContract $repository,
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
     * @param  DataReaderContract $repository
     * @param  int|string $id
     * @param  CriteriaContract|null $criteria
     * @param  DataMapperContract|null $dataMapper
     * @return object|null
     */
    public function getOneById(
        DataReaderContract $repository,
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
     * @param  DataReaderContract $repository
     * @param  CriteriaContract|null $criteria
     * @param  DataMapperContract|null $dataMapper
     * @return object|null
     */
    public function getFirst(
        DataReaderContract $repository,
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
     * Fetches criteria parameters from request and creates a new criteria object
     * or fills the given one.
     *
     * @param  CriteriaContract|null $criteria
     * @return CriteriaContract
     */
    protected function getRequestCriteria(?CriteriaContract $criteria = null): CriteriaContract
    {
        $criteriaValidator = new CriteriaValidator();
        $criteriaValidator->validate();

        if (is_null($criteria)) {
            $criteria = App::make(CriteriaContract::class);
        }

        $criteriaValidator->fillValidated($criteria);

        return $criteria;
    }
}
