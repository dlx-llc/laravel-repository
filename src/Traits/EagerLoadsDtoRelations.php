<?php

namespace LaravelRepository\Traits;

use LaravelRepository\Contracts\DtoContract;
use LaravelRepository\Contracts\RepositoryContract;

/**
 * Contains methods that'll let you eager load relations and relation counts
 * used by the data transfer object.
 */
trait EagerLoadsDtoRelations
{
    /**
     * Eager loads relations used by the data transfer object.
     *
     * @param  RepositoryContract $repository
     * @param  string $dto
     * @return void
     */
    protected function eagerLoadRelations(RepositoryContract $repository, string $dto): void
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
}
