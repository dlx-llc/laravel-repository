<?php

namespace LaravelRepository;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use LaravelRepository\Rules\RepositorySorting;
use LaravelRepository\Rules\RepositoryFiltration;
use LaravelRepository\Rules\RepositoryTextSearch;

class SearchCriteria
{
    use Traits\SupportsSorting;
    use Traits\SupportsTextSearch;
    use Traits\SupportsFiltration;

    /**
     * Crates a search criteria using parameters passed by the request.
     *
     * @param  string $textSearchKey
     * @param  string $sortingKey
     * @param  string $filtersKey
     * @param  bool $validate
     * @return static
     */
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
    public static function validate(
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

    /**
     * Constructor.
     *
     * @param  TextSearch|string|null $textSearch
     * @param  Sorting|string|null $sorting
     * @param  FilterGroup|string|null $filters
     * @return void
     */
    public function __construct(
        TextSearch|string|null $textSearch = null,
        Sorting|string|null $sorting = null,
        FilterGroup|string|null $filters = null
    ) {
        if (is_string($textSearch)) {
            $this->setSearchRaw($textSearch);
        } elseif ($textSearch) {
            $this->textSearch = $textSearch;
        }

        if (is_string($sorting)) {
            $this->setSortingRaw($sorting);
        } elseif ($sorting) {
            $this->sorting = $sorting;
        }

        if (is_string($filters)) {
            $this->setFiltersRaw($filters);
        } elseif ($filters) {
            $this->filters = $filters;
        }
    }
}
