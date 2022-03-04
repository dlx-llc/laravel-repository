<?php

namespace LaravelRepository;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use LaravelRepository\Rules\RepositorySorting;
use LaravelRepository\Contracts\SortingContract;
use LaravelRepository\Rules\RepositoryFiltration;
use LaravelRepository\Rules\RepositoryTextSearch;
use LaravelRepository\Contracts\TextSearchContract;
use LaravelRepository\Contracts\SearchCriteriaContract;
use LaravelRepository\Contracts\FiltersCollectionContract;

class SearchCriteria implements SearchCriteriaContract
{
    use Traits\SupportsSorting;
    use Traits\SupportsTextSearch;
    use Traits\SupportsFiltration;

    /** @inheritdoc */
    public static function makeFromRequest(
        string $textSearchKey = 'search',
        string $sortingKey = 'sort',
        string $filtersKey = 'filters',
        bool $validate = true
    ): static {
        $textSearch = Request::input($textSearchKey);
        $sorting = Request::input($sortingKey);
        $filters = Request::input($filtersKey);

        if ($validate) {
            static::validate(
                $textSearch,
                $sorting,
                $filters,
                $textSearchKey,
                $sortingKey,
                $filtersKey
            );
        }

        return new static($textSearch, $sorting, $filters);
    }

    /**
     * Validates search criteria params.
     *
     * @param  string|null $textSearch
     * @param  string|null $sorting
     * @param  string|null $filters
     * @return void
     * @throws ValidationException
     */
    protected static function validate(
        ?string $textSearch = null,
        ?string $sorting = null,
        ?string $filters = null,
        string $textSearchKey = 'search',
        string $sortingKey = 'sort',
        string $filtersKey = 'filters'
    ): void {
        $data = $rules = [];

        if (!is_null($textSearch)) {
            $data[$textSearchKey] = $textSearch;
            $rules[$textSearchKey] = [new RepositoryTextSearch()];
        }

        if (!is_null($sorting)) {
            $data[$sortingKey] = $sorting;
            $rules[$sortingKey] = [new RepositorySorting()];
        }

        if (!is_null($filters)) {
            $data[$filtersKey] = $filters;
            $rules[$filtersKey] = [new RepositoryFiltration()];
        }

        if ($data && $rules) {
            Validator::make($data, $rules)->validate();
        }
    }

    /** @inheritdoc */
    public function __construct(
        TextSearchContract|string|null $textSearch = null,
        SortingContract|string|null $sorting = null,
        FiltersCollectionContract|string|null $filters = null
    ) {
        if (is_string($textSearch)) {
            $this->setTextSearchRaw($textSearch);
        } elseif ($textSearch) {
            $this->setTextSearch($textSearch);
        }

        if (is_string($sorting)) {
            $this->setSortingRaw($sorting);
        } elseif ($sorting) {
            $this->setSorting($sorting);
        }

        if (is_string($filters)) {
            $this->setFiltersRaw($filters);
        } elseif ($filters) {
            $this->setFilters($filters);
        }
    }
}
