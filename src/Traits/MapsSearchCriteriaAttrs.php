<?php

namespace LaravelRepository\Traits;

use LaravelRepository\SearchCriteria;
use LaravelRepository\Contracts\FilterContract;
use LaravelRepository\Contracts\DataAttrContract;
use LaravelRepository\Contracts\DtoAttrMapContract;
use LaravelRepository\Contracts\FiltersCollectionContract;

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
    protected function mapSearchCriteriaAttrs(
        SearchCriteria $searchCriteria,
        string $dto
    ): void {
        $map = $dto::attrMap();

        if (!$map) {
            return;
        }

        if ($searchCriteria->textSearch) {
            $attrs = $searchCriteria->textSearch->getAttrs();

            foreach ($attrs as $attr) {
                $this->replaceAttrName($attr, $map);
            }
        }

        if ($searchCriteria->sorting) {
            $attr = $searchCriteria->sorting->getAttr();
            $this->replaceAttrName($attr, $map);
        }

        if ($searchCriteria->filters) {
            $this->mapFilterAttr($searchCriteria->filters, $map);
        }
    }

    /**
     * Maps filter and filter group attribute names recursively.
     *
     * @param  FiltersCollectionContract|FilterContract $filter
     * @param  DtoAttrMapContract $map
     * @return void
     */
    protected function mapFilterAttr(
        FiltersCollectionContract|FilterContract $filter,
        DtoAttrMapContract $map
    ): void {
        if (is_a($filter, FiltersCollection::class)) {
            foreach ($filter as $item) {
                $this->mapFilterAttr($item, $map);
            }
        } else {
            $attr = $filter->getAttr();
            $this->replaceAttrName($attr, $map);
        }
    }

    /**
     * Replaces the attribute name according to the given attributes map.
     *
     * @param  DataAttrContract $attr
     * @param  DtoAttrMapContract $map
     * @return void
     */
    protected function replaceAttrName(
        DataAttrContract $attr,
        DtoAttrMapContract $map
    ): void {
        $attrName = $attr->getNameWithRelation();
        $attrName = $map->match($attrName);
        $attr->setName($attrName);
    }
}
