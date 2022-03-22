<?php

namespace Deluxetech\LaRepo\Traits;

use Illuminate\Support\Collection;
use Deluxetech\LaRepo\CriteriaFactory;
use Deluxetech\LaRepo\PaginationFactory;
use Illuminate\Contracts\Pagination\Paginator;
use Deluxetech\LaRepo\Contracts\CriteriaContract;
use Deluxetech\LaRepo\Contracts\DataMapperContract;
use Deluxetech\LaRepo\Contracts\DataReaderContract;
use Deluxetech\LaRepo\Contracts\PaginationContract;
use Deluxetech\LaRepo\Contracts\LoadContextContract;

/**
 * Contains methods that make it easy to retrieve data from repositories by
 * applying query criteria, pagination, data mapping and load context.
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
        if ($dataMapper) {
            $repository->setDataMapper($dataMapper);
        }

        if ($criteria) {
            $repository->setCriteria($criteria);
        }

        return !is_null($pagination)
            ? $repository->paginate($pagination)
            : $repository->get();
    }

    /**
     * Fetches data collection from the given repository using request params.
     *
     * @param  DataReaderContract $repository
     * @param  DataMapperContract|null $dataMapper
     * @param  LoadContextContract|null $loadContext
     * @param  bool $pageRequired  TRUE by default.
     * @return Paginator|Collection
     */
    public function getManyWithRequest(
        DataReaderContract $repository,
        ?DataMapperContract $dataMapper = null,
        ?LoadContextContract $loadContext = null,
        bool $pageRequired = true
    ): Paginator|Collection {
        return $this->getMany(
            repository: $repository,
            criteria: CriteriaFactory::createFromRequest($loadContext),
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
        if ($dataMapper) {
            $repository->setDataMapper($dataMapper);
        }

        if ($criteria = CriteriaFactory::createFromRequest()) {
            $repository->setCriteria($criteria);
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
        if ($dataMapper) {
            $repository->setDataMapper($dataMapper);
        }

        if ($criteria) {
            $repository->setCriteria($criteria);
        }

        return $repository->find($id);
    }

    /**
     * Fetches a single data model from the given repository.
     *
     * @param  DataReaderContract $repository
     * @param  CriteriaContract|null $criteria,
     * @param  DataMapperContract|null $dataMapper
     * @return object|null
     */
    public function getFirst(
        DataReaderContract $repository,
        ?CriteriaContract $criteria = null,
        ?DataMapperContract $dataMapper = null
    ): ?object {
        if ($dataMapper) {
            $repository->setDataMapper($dataMapper);
        }

        if ($criteria) {
            $repository->setCriteria($criteria);
        }

        return $repository->first();
    }
}
