<?php

namespace LaravelRepository\Traits;

use LaravelRepository\Filter;
use LaravelRepository\Pagination;
use Illuminate\Support\Collection;
use LaravelRepository\DataAttrMap;
use LaravelRepository\DtoContract;
use LaravelRepository\FilterGroup;
use LaravelRepository\SearchContext;
use LaravelRepository\GenericRepository;
use Illuminate\Contracts\Pagination\Paginator;

trait FetchesRepositoryData
{
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
     * Eager loads relations used by the data transfer object.
     *
     * @param  GenericRepository $repository
     * @param  string $dto
     * @return void
     */
    protected function eagerLoadRelations(GenericRepository $repository, string $dto): void
    {
        $eagerLoadArgs = $this->makeEagerLoadArgs($dto);
        $repository->with($eagerLoadArgs);

        if ($countArgs = $dto::usedRelationCounts()) {
            $repository->withCount($countArgs);
        }
    }

    /**
     * Makes an array of the relations that should be eager loaded.
     *
     * @param  string $dto
     * @return array
     */
    protected function makeEagerLoadArgs(string $dto): array
    {
        $args = [];
        $usedRelations = $dto::usedRelations();

        foreach ($usedRelations as $relation => $relDto) {
            if (is_int($relation)) {
                $args[] = $relDto;
            } elseif (is_string($relation)) {
                if (is_subclass_of($relDto, DtoContract::class)) {
                    if ($subArgs = $this->makeEagerLoadArgs($relDto)) {
                        $args[$relation] = fn($q) => $q->with($subArgs);
                    } else {
                        $args[] = $relation;
                    }
                } elseif (is_callable($relDto)) {
                    $args[$relation] = $relDto;
                }
            }
        }

        return $args;
    }

    /**
     * Replaces the search context public attributes with the corresponding
     * internal attributes.
     *
     * @param  SearchContext $searchContext
     * @param  string $dto
     * @return void
     */
    protected function mapSearchContextAttrs(SearchContext $searchContext, string $dto): void
    {
        $map = $dto::attrMap();

        if (!$map) {
            return;
        }

        if ($searchContext->textSearch) {
            foreach ($searchContext->textSearch->attrs as $i => $attr) {
                $searchContext->textSearch->attrs[$i] = $map->match($attr);
            }
        }

        if ($searchContext->sorting) {
            $attr = $map->match($searchContext->sorting->getAttr());
            $searchContext->sorting->setAttr($attr);
        }

        if ($searchContext->filters) {
            $this->mapFilterAttr($searchContext->filters, $map);
        }
    }

    /**
     * Maps filter and filter group data attributes recursively.
     *
     * @param  FilterGroup|Filter $filter
     * @param  DataAttrMap $map
     * @return void
     */
    protected function mapFilterAttr(FilterGroup|Filter $filter, DataAttrMap $map): void
    {
        if (is_a($filter, FilterGroup::class)) {
            if ($filter->relation) {
                $filter->relation = $map->match($filter->relation);
            }

            foreach ($filter as $item) {
                $this->mapFilterAttr($item, $map);
            }
        } else {
            $attr = $map->match($filter->getAttr());
            $filter->setAttr($attr);
        }
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
