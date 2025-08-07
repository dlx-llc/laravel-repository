<?php

namespace Deluxetech\LaRepo\Facades;

use Deluxetech\LaRepo\Contracts\CriteriaContract;
use Deluxetech\LaRepo\Contracts\DataAttrContract;
use Deluxetech\LaRepo\Contracts\DataMapperContract;
use Deluxetech\LaRepo\Contracts\FilterContract;
use Deluxetech\LaRepo\Contracts\FiltersCollectionContract;
use Deluxetech\LaRepo\Contracts\PaginationContract;
use Deluxetech\LaRepo\Contracts\RepositoryContract;
use Deluxetech\LaRepo\Contracts\RequestQueryContract;
use Deluxetech\LaRepo\Contracts\SortingContract;
use Deluxetech\LaRepo\Contracts\TextSearchContract;
use Deluxetech\LaRepo\Enums\BooleanOperator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Paginator|Collection getMany(RepositoryContract $repository, ?CriteriaContract $criteria = null, ?PaginationContract $pagination = null, ?DataMapperContract $dataMapper = null)  Fetches data collection from the given repository.
 * @method static Paginator|Collection getManyWithRequest(RepositoryContract $repository, ?CriteriaContract $criteria = null, ?DataMapperContract $dataMapper = null, bool $pageRequired = true)  Fetches data collection from the given repository using request params.
 * @method static int getCountWithRequest(RepositoryContract $repository, ?DataMapperContract $dataMapper = null)  Fetches data count from the given repository using request params.
 * @method static ?object getOneById(RepositoryContract $repository, int|string $id, ?CriteriaContract $criteria = null, ?DataMapperContract $dataMapper = null)  Fetches a single data model from the given repository by ID.
 * @method static ?object getFirst(RepositoryContract $repository, ?CriteriaContract $criteria = null, ?DataMapperContract $dataMapper = null)  Fetches a single data model from the given repository.
 * @method static PaginationContract getRequestPagination(bool $require = true, ?int $perPageMax = null, ?string $pageKey = null, ?string $perPageKey = null)  Creates a new pagination object using the parameters of the request.
 * @method static CriteriaContract getRequestCriteria(?CriteriaContract $criteria = null, ?string $textSearchKey = null, ?string $sortingKey = null, ?string $filtersKey = null)  Fetches criteria parameters from the request and creates a new criteria object or fills the given one.
 * @method static RequestQueryContract getRequestQuery(?CriteriaContract $criteria = null, ?DataMapperContract $dataMapper = null, array<int|string,mixed> $extraParameters = [] bool $requirePagination = true)  Creates a new request query object using the parameters of the request. Non-package related parameters can be passed in extra parameters.
 * @method static CriteriaContract newCriteria()  Creates a new query criteria object.
 * @method static DataMapperContract newDataMapper()  Creates a new data mapper object.
 * @method static FilterContract newFilter(string $attr, string $operator, mixed $value, string $boolean = BooleanOperator::AND)  Creates a new filter object.
 * @method static FiltersCollectionContract newFiltersCollection(string $boolean = BooleanOperator::AND, FiltersCollectionContract|FilterContract ...$items)  Creates a new filters collection object.
 * @method static SortingContract newSorting(DataAttrContract|string $attr, string $dir)  Creates a new sorting object.
 * @method static TextSearchContract newTextSearch(string $text, DataAttrContract|string ...$attrs)  Creates a new text search object.
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
