<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Contracts;

use Illuminate\Contracts\Support\Arrayable;
use Stringable;

/**
 * @implements Arrayable<string, int|string>
 */
interface RequestQueryContract extends Stringable, Arrayable
{
    public function setPagination(?PaginationContract $pagination): static;

    public function addCriteria(CriteriaContract $criteria): static;

    public function applyDataMapper(DataMapperContract $dataMapper): static;

    public function useTextSearchKey(string $key): static;

    public function useSortingKey(string $key): static;

    public function useFiltersKey(string $key): static;
}
