<?php

namespace Deluxetech\LaRepo\Traits;

use Illuminate\Support\Collection;
use Deluxetech\LaRepo\PaginationFactory;
use Deluxetech\LaRepo\SearchCriteriaFactory;
use Illuminate\Contracts\Pagination\Paginator;
use Deluxetech\LaRepo\Contracts\DataMapperContract;
use Deluxetech\LaRepo\Contracts\PaginationContract;
use Deluxetech\LaRepo\Contracts\LoadContextContract;
use Deluxetech\LaRepo\Contracts\SearchCriteriaContract;
use Deluxetech\LaRepo\Contracts\ImmutableRepositoryContract;

/**
 * Contains methods that make it easy to retrieve data from repositories by
 * applying search criteria, pagination, data mapping and load context.
 */
trait FetchesRepositoryData
{
    /**
     * Fetches data collection from the given repository.
     *
     * @param  ImmutableRepositoryContract $repository
     * @param  SearchCriteriaContract|null $searchCriteria
     * @param  PaginationContract|null $pagination
     * @param  DataMapperContract|null $dataMapper
     * @param  LoadContextContract|null $loadContext
     * @return Paginator|Collection
     */
    public function getMany(
        ImmutableRepositoryContract $repository,
        ?SearchCriteriaContract $searchCriteria = null,
        ?PaginationContract $pagination = null,
        ?DataMapperContract $dataMapper = null,
        ?LoadContextContract $loadContext = null
    ): Paginator|Collection {
        if ($dataMapper) {
            $repository->setDataMapper($dataMapper);
        }

        if ($loadContext) {
            $repository->setLoadContext($loadContext);
        }

        if ($searchCriteria) {
            $repository->search($searchCriteria);
        }

        return !is_null($pagination)
            ? $repository->paginate($pagination)
            : $repository->get();
    }

    /**
     * Fetches data collection from the given repository using request params.
     *
     * @param  ImmutableRepositoryContract $repository
     * @param  DataMapperContract|null $dataMapper
     * @param  LoadContextContract|null $loadContext
     * @param  bool $pageRequired  TRUE by default.
     * @return Paginator|Collection
     */
    public function getManyWithRequest(
        ImmutableRepositoryContract $repository,
        ?DataMapperContract $dataMapper = null,
        ?LoadContextContract $loadContext = null,
        bool $pageRequired = true
    ): Paginator|Collection {
        return $this->getMany(
            repository: $repository,
            searchCriteria: SearchCriteriaFactory::createFromRequest(),
            pagination: PaginationFactory::createFromRequest(require: $pageRequired),
            dataMapper: $dataMapper,
            loadContext: $loadContext
        );
    }

    /**
     * Fetches a single data model from the given repository by ID.
     *
     * @param  ImmutableRepositoryContract $repository
     * @param  int|string $id
     * @param  DataMapperContract|null $dataMapper
     * @param  LoadContextContract|null $loadContext
     * @return mixed
     */
    public function getOneById(
        ImmutableRepositoryContract $repository,
        int|string $id,
        ?DataMapperContract $dataMapper = null,
        ?LoadContextContract $loadContext = null
    ): mixed {
        if ($dataMapper) {
            $repository->setDataMapper($dataMapper);
        }

        if ($loadContext) {
            $repository->setLoadContext($loadContext);
        }

        return $repository->find($id);
    }

    /**
     * Fetches a single data model from the given repository.
     *
     * @param  ImmutableRepositoryContract $repository
     * @param  SearchCriteriaContract|null $searchCriteria,
     * @param  DataMapperContract|null $dataMapper
     * @param  LoadContextContract|null $loadContext
     * @return mixed
     */
    public function getFirst(
        ImmutableRepositoryContract $repository,
        ?SearchCriteriaContract $searchCriteria = null,
        ?DataMapperContract $dataMapper = null,
        ?LoadContextContract $loadContext = null
    ): mixed {
        if ($dataMapper) {
            $repository->setDataMapper($dataMapper);
        }

        if ($loadContext) {
            $repository->setLoadContext($loadContext);
        }

        if ($searchCriteria) {
            $repository->search($searchCriteria);
        }

        return $repository->first();
    }
}
