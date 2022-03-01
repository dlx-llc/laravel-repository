<?php

namespace LaravelRepository\Traits;

use LaravelRepository\Filter;
use LaravelRepository\FilterGroup;
use LaravelRepository\SearchCriteria;
use LaravelRepository\Contracts\DtoAttrMapContract;

/**
 * Contains methods that let you replace data attributes in the search
 * criteria parameters in correspondence with the provided data transfer object.
 */
trait MapsSearchCriteriaAttrs
{
    /**
     * Replaces the search criteria public attributes with the corresponding
     * internal attributes.
     *
     * @param  SearchCriteria $searchCriteria
     * @param  string $dto
     * @return void
     */
    protected function mapSearchCriteriaAttrs(SearchCriteria $searchCriteria, string $dto): void
    {
        $map = $dto::attrMap();

        if (!$map) {
            return;
        }

        if ($searchCriteria->textSearch) {
            foreach ($searchCriteria->textSearch->attrs as $i => $attr) {
                $searchCriteria->textSearch->attrs[$i] = $map->match($attr);
            }
        }

        if ($searchCriteria->sorting) {
            $attr = $map->match($searchCriteria->sorting->getAttr());
            $searchCriteria->sorting->setAttr($attr);
        }

        if ($searchCriteria->filters) {
            $this->mapFilterAttr($searchCriteria->filters, $map);
        }
    }

    /**
     * Maps filter and filter group attribute names recursively.
     *
     * @param  FilterGroup|Filter $filter
     * @param  DtoAttrMapContract $map
     * @return void
     */
    protected function mapFilterAttr(FilterGroup|Filter $filter, DtoAttrMapContract $map): void
    {
        if (is_a($filter, FilterGroup::class)) {
            if ($filter->relation) {
                $filter->relation = $map->match($filter->relation);
            }

            foreach ($filter as $item) {
                $this->mapFilterAttr($item, $map);
            }
        } else {
            $attr = $map->match($filter->getAttr());
            $filter->setAttr($attr);
        }
    }
}
