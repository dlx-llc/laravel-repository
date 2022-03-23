<?php

namespace Deluxetech\LaRepo\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Illuminate\Contracts\Pagination\Paginator;
use Deluxetech\LaRepo\Contracts\CriteriaContract;
use Deluxetech\LaRepo\Contracts\DataMapperContract;
use Deluxetech\LaRepo\Contracts\DataReaderContract;
use Deluxetech\LaRepo\Contracts\PaginationContract;

/**
 * @method static Paginator|Collection getMany(DataReaderContract $repository, ?CriteriaContract $criteria = null, ?PaginationContract $pagination = null, ?DataMapperContract $dataMapper = null)  Fetches data collection from the given repository.
 * @method static Paginator|Collection getManyWithRequest(DataReaderContract $repository, ?CriteriaContract $criteria = null, ?DataMapperContract $dataMapper = null, bool $pageRequired = true)  Fetches data collection from the given repository using request params.
 * @method static int getCountWithRequest(DataReaderContract $repository, ?DataMapperContract $dataMapper = null)  Fetches data count from the given repository using request params.
 * @method static ?object getOneById(DataReaderContract $repository, int|string $id, ?CriteriaContract $criteria = null, ?DataMapperContract $dataMapper = null)  Fetches a single data model from the given repository by ID.
 * @method static ?object getFirst(DataReaderContract $repository, ?CriteriaContract $criteria = null, ?DataMapperContract $dataMapper = null)  Fetches a single data model from the given repository.
 * @method static PaginationContract getRequestPagination(bool $require = true, ?int $perPageMax = null, ?string $pageKey = null, ?string $perPageKey = null)  Creates a new pagination object using the parameters of the request.
 * @method static CriteriaContract getRequestCriteria(?CriteriaContract $criteria = null, ?string $textSearchKey = null, ?string $sortingKey = null, ?string $filtersKey = null)  Fetches criteria parameters from the request and creates a new criteria object or fills the given one.
 *
 * @see \Deluxetech\LaRepo\RepositoryUtils
 */
class LaRepo extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'larepo-utils';
    }
}
