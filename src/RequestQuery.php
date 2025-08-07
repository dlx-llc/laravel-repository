<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo;

use Deluxetech\LaRepo\Contracts\CriteriaContract;
use Deluxetech\LaRepo\Contracts\DataMapperContract;
use Deluxetech\LaRepo\Contracts\FiltersCollectionFormatterContract;
use Deluxetech\LaRepo\Contracts\PaginationContract;
use Deluxetech\LaRepo\Contracts\RequestQueryContract;
use Deluxetech\LaRepo\Contracts\SortingFormatterContract;
use Deluxetech\LaRepo\Contracts\TextSearchFormatterContract;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

class RequestQuery implements RequestQueryContract
{
    protected ?CriteriaContract $criteria = null;
    protected ?PaginationContract $pagination = null;
    protected string $textSearchKey;
    protected string $sortingKey;
    protected string $filtersKey;

    /**
     * @var array<int|string,mixed>
     */
    protected array $extraParameters = [];

    public function __construct()
    {
        $this->textSearchKey = Config::get('larepo.request_text_search_key');
        $this->sortingKey = Config::get('larepo.request_sorting_key');
        $this->filtersKey = Config::get('larepo.request_filters_key');
    }

    public function fillFromArray(array $query): static
    {
        $this->extraParameters = [
            ...$this->extraParameters,
            ...$query,
        ];

        return $this;
    }

    public function setPagination(?PaginationContract $pagination): static
    {
        $this->pagination = $pagination;

        return $this;
    }

    public function addCriteria(CriteriaContract $criteria): static
    {
        if ($this->criteria) {
            $this->criteria->merge($criteria);
        } else {
            $this->criteria = $criteria;
        }

        return $this;
    }

    public function applyDataMapper(DataMapperContract $dataMapper): static
    {
        $dataMapper->applyOnCriteria($this->criteria);

        return $this;
    }

    public function useTextSearchKey(string $key): static
    {
        $this->textSearchKey = $key;

        return $this;
    }

    public function useSortingKey(string $key): static
    {
        $this->sortingKey = $key;

        return $this;
    }

    public function useFiltersKey(string $key): static
    {
        $this->filtersKey = $key;

        return $this;
    }

    public function toArray(): array
    {
        $query = $this->extraParameters;

        if ($this->pagination) {
            $query[$this->pagination->getPageName()] = $this->pagination->getPage();
            $query[$this->pagination->getPerPageName()] = $this->pagination->getPerPage();
        }

        if ($sorting = $this->criteria?->getSorting()) {
            $sorting = App::make(SortingFormatterContract::class)->stringify($sorting);
            $query[$this->sortingKey] = $sorting;
        }

        if ($textSearch = $this->criteria?->getTextSearch()) {
            $textSearch = App::make(TextSearchFormatterContract::class)->stringify($textSearch);
            $query[$this->textSearchKey] = $textSearch;
        }

        if ($filters = $this->criteria?->getFilters()) {
            $filters = App::make(FiltersCollectionFormatterContract::class)->stringify($filters);
            $query[$this->filtersKey] = $filters;
        }

        return $query;
    }

    public function __toString(): string
    {
        return http_build_query($this->toArray(), '', '&', PHP_QUERY_RFC3986);
    }
}
