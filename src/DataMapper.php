<?php

namespace Deluxetech\LaRepo;

use Deluxetech\LaRepo\Enums\FilterMode;
use Deluxetech\LaRepo\Contracts\FilterContract;
use Deluxetech\LaRepo\Contracts\CriteriaContract;
use Deluxetech\LaRepo\Contracts\DataMapperContract;
use Deluxetech\LaRepo\Contracts\FiltersCollectionContract;

class DataMapper implements DataMapperContract
{
    /**
     * A map of domain model attributes to source data attributes.
     *
     * @var array
     */
    protected array $map = [];

    /**
     * Sub maps.
     *
     * @var array
     */
    protected array $subMaps = [];

    /** @inheritdoc */
    public function set(
        string $domainAttr,
        string $dataAttr,
        ?DataMapperContract $subMap = null
    ): static {
        $this->map[$domainAttr] = $dataAttr;

        if ($subMap) {
            $this->subMaps[$domainAttr] = $subMap;
        }

        return $this;
    }

    /** @inheritdoc */
    public function get(string $domainAttr): string
    {
        // Returns the direct match if set.
        if (isset($this->map[$domainAttr])) {
            return $this->map[$domainAttr];
        }

        // When a multilevel attribute is given, looks for a match for each level.
        if (str_contains($domainAttr, '.')) {
            $firstDotPos = strpos($domainAttr, '.');
            $base = substr($domainAttr, 0, $firstDotPos);
            $sub = substr($domainAttr, $firstDotPos + 1);

            if (isset($this->subMaps[$base])) {
                $subMap = $this->subMaps[$base];
                $sub = $subMap->get($sub);
            }

            $base = $this->get($base);

            return $base . '.' . $sub;
        }

        return $domainAttr;
    }


    /** @inheritdoc */
    public function applyOnCriteria(CriteriaContract $criteria): void
    {
        if ($textSearch = $criteria->getTextSearch()) {
            foreach ($textSearch->getAttrs() as $attr) {
                $this->replaceDataAttrName($attr);
            }
        }

        if ($sorting = $criteria->getSorting()) {
            $attr = $sorting->getAttr();
            $this->replaceDataAttrName($attr);
        }

        if ($filters = $criteria->getFilters()) {
            $this->replaceFilterAttrName($filters);
        }
    }

    /**
     * Recursively maps domain model attributes of the given filter.
     *
     * @param  FiltersCollectionContract|FilterContract $filter
     * @param  string|null $prefix
     * @return void
     */
    protected function replaceFilterAttrName(
        FiltersCollectionContract|FilterContract $filter,
        ?string $prefix = null
    ): void {
        if (is_a($filter, FiltersCollectionContract::class)) {
            foreach ($filter as $item) {
                $this->replaceFilterAttrName($item, $prefix);
            }
        } elseif (
            in_array($filter->getMode(), [
                FilterMode::EXISTS,
                FilterMode::DOES_NOT_EXIST,
            ])
        ) {
            $items = $filter->getValue();
            $prefix = $filter->getAttr()->getName();

            /** @var FilterContract|FiltersCollectionContract $item */
            foreach ($items as $item) {
                $this->replaceFilterAttrName($item, $prefix);
            }
        } else {
            $attr = $filter->getAttr();
            $this->replaceDataAttrName($attr, $prefix);
        }
    }

    /**
     * Replaces the domain model attribute  data attributes map if set.
     *
     * @param  DataAttr $attr
     * @param  string|null $prefix
     * @return void
     */
    protected function replaceDataAttrName(DataAttr $attr, ?string $prefix = null): void
    {
        if ($prefix) {
            $attr->addFromBeginning($prefix);
        }

        $attrName = $attr->getName();
        $newName = $this->get($attrName);
        $attr->setName($newName);

        if ($prefix) {
            $attr->removeFromBeginning($prefix);
        }
    }
}
