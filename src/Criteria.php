<?php

namespace Deluxetech\LaRepo;

use Deluxetech\LaRepo\Contracts\CriteriaContract;
use Deluxetech\LaRepo\Contracts\FiltersCollectionFormatterContract;
use Deluxetech\LaRepo\Contracts\SortingFormatterContract;
use Deluxetech\LaRepo\Contracts\TextSearchFormatterContract;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

class Criteria implements CriteriaContract
{
    use Traits\SupportsSorting;
    use Traits\SupportsTextSearch;
    use Traits\SupportsFiltration;
    use Traits\DefinesQueryContext;

    public function merge(CriteriaContract $criteria): static
    {
        if ($attributes = $criteria->getAttributes()) {
            $attributes = array_unique([...$this->getAttributes(), ...$attributes]);
            $this->setAttributes(...$attributes);
        }

        if ($relations = $criteria->getRelations()) {
            foreach ($relations as $relation => $relCriteria) {
                $this->addRelation($relation, $relCriteria);
            }
        }

        if ($counts = $criteria->getRelationCounts()) {
            foreach ($counts as $relation => $relCriteria) {
                $this->addRelationCount($relation, $relCriteria);
            }
        }

        if ($sorting = $criteria->getSorting()) {
            $this->setSorting($sorting);
        }

        if ($textSearch = $criteria->getTextSearch()) {
            $this->setTextSearch($textSearch);
        }

        if ($filters = $criteria->getFilters()) {
            if (is_null($this->filters)) {
                $this->filters = $filters->clone();
            } else {
                $this->filters->add($filters);
            }
        }

        return $this;
    }

    public function clone(): static
    {
        $clone = new static();
        $clone->setAttributes(...$this->getAttributes());
        $clone->setRelations($this->getRelations());
        $clone->setRelationCounts($this->getRelationCounts());
        $clone->setSorting($this->getSorting());
        $clone->setTextSearch($this->getTextSearch());
        $clone->setFilters($this->getFilters()?->clone());

        return $clone;
    }

    public function toRequestQueryArray(
        ?string $textSearchKey = null,
        ?string $sortingKey = null,
        ?string $filtersKey = null,
    ): array {
        $textSearchKey = $textSearchKey ?? Config::get('larepo.request_text_search_key');
        $sortingKey = $sortingKey ?? Config::get('larepo.request_sorting_key');
        $filtersKey = $filtersKey ?? Config::get('larepo.request_filters_key');
        $query = [];

        if ($sorting = $this->getSorting()) {
            $sorting = App::make(SortingFormatterContract::class)->stringify($sorting);
            $query[$sortingKey] = $sorting;
        }

        if ($textSearch = $this->getTextSearch()) {
            $textSearch = App::make(TextSearchFormatterContract::class)->stringify($textSearch);
            $query[$textSearchKey] = $textSearch;
        }

        if ($filters = $this->getFilters()) {
            $filters = App::make(FiltersCollectionFormatterContract::class)->stringify($filters);
            $query[$filtersKey] = $filters;
        }

        return $query;
    }
}
