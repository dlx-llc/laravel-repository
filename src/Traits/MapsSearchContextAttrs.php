<?php

namespace LaravelRepository\Traits;

use LaravelRepository\Filter;
use LaravelRepository\DataAttrMap;
use LaravelRepository\FilterGroup;
use LaravelRepository\SearchContext;

trait MapsSearchContextAttrs
{

    /**
     * Replaces the search context public attributes with the corresponding
     * internal attributes.
     *
     * @param  SearchContext $searchContext
     * @param  string $dto
     * @return void
     */
    protected function mapSearchContextAttrs(SearchContext $searchContext, string $dto): void
    {
        $map = $dto::attrMap();

        if (!$map) {
            return;
        }

        if ($searchContext->textSearch) {
            foreach ($searchContext->textSearch->attrs as $i => $attr) {
                $searchContext->textSearch->attrs[$i] = $map->match($attr);
            }
        }

        if ($searchContext->sorting) {
            $attr = $map->match($searchContext->sorting->getAttr());
            $searchContext->sorting->setAttr($attr);
        }

        if ($searchContext->filters) {
            $this->mapFilterAttr($searchContext->filters, $map);
        }
    }

    /**
     * Maps filter and filter group data attributes recursively.
     *
     * @param  FilterGroup|Filter $filter
     * @param  DataAttrMap $map
     * @return void
     */
    protected function mapFilterAttr(FilterGroup|Filter $filter, DataAttrMap $map): void
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
