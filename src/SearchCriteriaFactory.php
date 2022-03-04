<?php

namespace LaravelRepository;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use LaravelRepository\Rules\RepositorySorting;
use LaravelRepository\Contracts\SortingContract;
use LaravelRepository\Rules\RepositoryFiltration;
use LaravelRepository\Rules\RepositoryTextSearch;
use LaravelRepository\Contracts\TextSearchContract;
use LaravelRepository\Contracts\SearchCriteriaContract;
use LaravelRepository\Contracts\FiltersCollectionContract;

final class SearchCriteriaFactory
{
    /**
     * Creates a new search criteria object.
     *
     * @param  TextSearchContract|null $textSearch
     * @param  SortingContract|null $sorting
     * @param  FiltersCollectionContract|null $filters
     * @return SearchCriteriaContract
     */
    public static function create(
        TextSearchContract|null $textSearch = null,
        SortingContract|null $sorting = null,
        FiltersCollectionContract|null $filters = null
    ): SearchCriteriaContract {
        return App::makeWith(SearchCriteriaContract::class, [
            'textSearch' => $textSearch,
            'sorting' => $sorting,
            'filters' => $filters,
        ]);
    }

    /**
     * Crates a search criteria using parameters passed via request.
     *
     * @param  string $textSearchKey
     * @param  string $sortingKey
     * @param  string $filtersKey
     * @param  bool $validate
     * @return SearchCriteriaContract
     */
    public static function makeFromRequest(
        string $textSearchKey = 'search',
        string $sortingKey = 'sort',
        string $filtersKey = 'filters',
        bool $validate = true
    ): SearchCriteriaContract {
        $textSearch = Request::input($textSearchKey);
        $sorting = Request::input($sortingKey);
        $filters = Request::input($filtersKey);

        if ($validate) {
            self::validate(
                $textSearch,
                $sorting,
                $filters,
                $textSearchKey,
                $sortingKey,
                $filtersKey
            );
        }

        return self::create($textSearch, $sorting, $filters);
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
}