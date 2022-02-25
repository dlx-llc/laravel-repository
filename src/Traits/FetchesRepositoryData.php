<?php

namespace LaravelRepository\Traits;

use LaravelRepository\Pagination;
use Illuminate\Support\Collection;
use LaravelRepository\DtoContract;
use LaravelRepository\SearchContext;
use LaravelRepository\GenericRepository;
use Illuminate\Contracts\Pagination\Paginator;

/**
 * Contains methods that make it easy to retrieve data from repositories by
 * applying search context, pagination, and relationships eager loading.
 */
trait FetchesRepositoryData
{
    use EagerLoadsDtoRelations;
    use MapsSearchContextAttrs;

    /**
     * Fetches data collection from the given repository.
     *
     * @param  GenericRepository $repository
     * @param  SearchContext|null $searchContext
     * @param  Pagination|null $pagination
     * @param  string|null $dto
     * @return Paginator|Collection
     */
    public function getMany(
        GenericRepository $repository,
        ?SearchContext $searchContext = null,
        ?Pagination $pagination = null,
        ?string $dto = null
    ): Paginator|Collection {
        if ($dto) {
            $this->validateDto($dto);

            if ($searchContext) {
                $this->mapSearchContextAttrs($searchContext, $dto);
            }

            $this->eagerLoadRelations($repository, $dto);
        }

        if ($searchContext) {
            $repository->search($searchContext);
        }

        return !is_null($pagination)
            ? $repository->paginate($pagination)
            : $repository->get();
    }

    /**
     * Fetches a single data model from the given repository by ID.
     *
     * @param  GenericRepository $repository
     * @param  int|string $id
     * @param  string|null $dto
     * @return mixed
     */
    public function getOneById(
        GenericRepository $repository,
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
     * @param  GenericRepository $repository
     * @param  SearchContext|null $searchContext,
     * @param  string|null $dto
     * @return mixed
     */
    public function getFirst(
        GenericRepository $repository,
        ?SearchContext $searchContext = null,
        ?string $dto = null
    ): mixed {
        if ($dto) {
            $this->validateDto($dto);
            $this->eagerLoadRelations($repository, $dto);
        }

        if ($searchContext) {
            $repository->search($searchContext);
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
            throw new \Exception(__('lrepo::exceptions.invalid_dto_class'));
        }
    }
}
