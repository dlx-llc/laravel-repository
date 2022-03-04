<?php

namespace LaravelRepository;

use LaravelRepository\Contracts\SortingContract;
use LaravelRepository\Contracts\TextSearchContract;
use LaravelRepository\Contracts\SearchCriteriaContract;
use LaravelRepository\Contracts\FiltersCollectionContract;

class SearchCriteria implements SearchCriteriaContract
{
    use Traits\SupportsSorting;
    use Traits\SupportsTextSearch;
    use Traits\SupportsFiltration;

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
