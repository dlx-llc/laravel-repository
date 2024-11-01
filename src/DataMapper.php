<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo;

use Traversable;
use Deluxetech\LaRepo\Enums\FilterOperator;
use Deluxetech\LaRepo\Contracts\FilterContract;
use Deluxetech\LaRepo\Contracts\CriteriaContract;
use Deluxetech\LaRepo\Contracts\DataAttrContract;
use Deluxetech\LaRepo\Contracts\DataMapperContract;
use Deluxetech\LaRepo\Contracts\FiltersCollectionContract;

class DataMapper implements DataMapperContract
{
    /**
     * A map of domain model attributes to source data attributes.
     *
     * @var array<string,string>
     */
    protected array $map = [];

    /**
     * Sub maps.
     *
     * @var array<string,DataMapperContract>
     */
    protected array $subMaps = [];

    public function set(
        string $domainAttr,
        string $dataAttr,
        ?DataMapperContract $subMap = null,
    ): static {
        $this->map[$domainAttr] = $dataAttr;

        if ($subMap) {
            $this->subMaps[$domainAttr] = $subMap;
        }

        return $this;
    }

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
     */
    protected function replaceFilterAttrName(
        FiltersCollectionContract|FilterContract $filter,
        ?string $prefix = null,
    ): void {
        if (is_a($filter, FiltersCollectionContract::class)) {
            foreach ($filter as $item) {
                $this->replaceFilterAttrName($item, $prefix);
            }
        } else {
            $attr = $filter->getAttr();
            $this->replaceDataAttrName($attr, $prefix);

            if (
                in_array($filter->getOperator(), [
                    FilterOperator::EXISTS,
                    FilterOperator::DOES_NOT_EXIST,
                ], true)
            ) {
                $items = $filter->getValue();

                if (is_a($items, Traversable::class)) {
                    $prefix = $filter->getAttr()->getName();

                    foreach ($items as $item) {
                        $this->replaceFilterAttrName($item, $prefix);
                    }
                }
            }
        }
    }

    /**
     * Replaces the domain model attribute data attributes map if set.
     */
    protected function replaceDataAttrName(DataAttrContract $attr, ?string $prefix = null): void
    {
        if ($prefix) {
            $attr->addFromBeginning($prefix);
            $segmentsCount = $attr->countSegments();
        }

        $attrName = $attr->getName();
        $newName = $this->get($attrName);
        $attr->setName($newName);

        if ($prefix) {
            $newSegmentsCount = $attr->countSegments();

            if ($newSegmentsCount < $segmentsCount) {
                // Revert the change if the number of layers has been reduced.
                $attr->setName($attrName);
            }

            $attr->removeFromBeginning(...explode('.', $prefix));
        }
    }
}
