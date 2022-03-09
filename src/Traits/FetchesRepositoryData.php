<?php

namespace Deluxetech\LaRepo\Traits;

use Illuminate\Support\Collection;
use Deluxetech\LaRepo\PaginationFactory;
use Deluxetech\LaRepo\Contracts\DtoContract;
use Deluxetech\LaRepo\SearchCriteriaFactory;
use Illuminate\Contracts\Pagination\Paginator;
use Deluxetech\LaRepo\Contracts\PaginationContract;
use Deluxetech\LaRepo\Contracts\RepositoryContract;
use Deluxetech\LaRepo\Contracts\SearchCriteriaContract;

/**
 * Contains methods that make it easy to retrieve data from repositories by
 * applying search criteria, pagination, and relationships eager loading.
 */
trait FetchesRepositoryData
{
    use EagerLoadsDtoRelations;
    use MapsSearchCriteriaAttrs;

    /**
     * Fetches data collection from the given repository.
     *
     * @param  RepositoryContract $repository
     * @param  SearchCriteriaContract|null $searchCriteria
     * @param  PaginationContract|null $pagination
     * @param  string|null $dto
     * @return Paginator|Collection
     */
    public function getMany(
        RepositoryContract $repository,
        ?SearchCriteriaContract $searchCriteria = null,
        ?PaginationContract $pagination = null,
        ?string $dto = null
    ): Paginator|Collection {
        if ($dto) {
            $this->validateDto($dto);

            if ($searchCriteria) {
                $this->mapSearchCriteriaAttrs($searchCriteria, $dto);
            }

            $this->eagerLoadRelations($repository, $dto);
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
     * @param  RepositoryContract $repository
     * @param  string|null $dto
     * @return Paginator|Collection
     */
    public function getManyWithRequest(
        RepositoryContract $repository,
        ?string $dto = null
    ): Paginator|Collection {
        return $this->getMany(
            repository: $repository,
            searchCriteria: SearchCriteriaFactory::createFromRequest(),
            pagination: PaginationFactory::createFromRequest(require: true),
            dto: $dto
        );
    }

    /**
     * Fetches a single data model from the given repository by ID.
     *
     * @param  RepositoryContract $repository
     * @param  int|string $id
     * @param  string|null $dto
     * @return mixed
     */
    public function getOneById(
        RepositoryContract $repository,
        int|string $id,
        ?string $dto = null
    ): mixed {
        if ($dto) {
            $this->validateDto($dto);
            $this->eagerLoadRelations($repository, $dto);
        }

        return $repository->find($id);
    }

    /**
     * Fetches a single data model from the given repository.
     *
     * @param  RepositoryContract $repository
     * @param  SearchCriteriaContract|null $searchCriteria,
     * @param  string|null $dto
     * @return mixed
     */
    public function getFirst(
        RepositoryContract $repository,
        ?SearchCriteriaContract $searchCriteria = null,
        ?string $dto = null
    ): mixed {
        if ($dto) {
            $this->validateDto($dto);
            $this->eagerLoadRelations($repository, $dto);
        }

        if ($searchCriteria) {
            $repository->search($searchCriteria);
        }

        return $repository->first();
    }

    /**
     * Checks if the given class implements DTO interface.
     *
     * @param  string $class
     * @return void
     * @throws \Exception
     */
    protected function validateDto(string $class): void
    {
        if (!is_subclass_of($class, DtoContract::class)) {
            throw new \Exception(__('lrepo::exceptions.does_not_implement', [
                'class' => $class,
                'interface' => DtoContract::class,
            ]));
        }
    }
}
