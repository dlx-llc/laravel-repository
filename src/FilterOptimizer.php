<?php

namespace Deluxetech\LaRepo;

use Illuminate\Support\Facades\App;
use Deluxetech\LaRepo\Facades\LaRepo;
use Deluxetech\LaRepo\Enums\FilterOperator;
use Deluxetech\LaRepo\Enums\BooleanOperator;
use Deluxetech\LaRepo\Contracts\FilterContract;
use Deluxetech\LaRepo\Contracts\DataAttrContract;
use Deluxetech\LaRepo\Contracts\FilterOptimizerContract;
use Deluxetech\LaRepo\Contracts\FiltersCollectionContract;

class FilterOptimizer implements FilterOptimizerContract
{
    public function optimize(FiltersCollectionContract $collection): void
    {
        $result = $this->optimizeOrOmit($collection);

        if (is_null($result)) {
            $collection->setItems();
        } elseif (is_array($result)) {
            $collection->setItems(...$result);
        } elseif (is_a($result, FilterContract::class)) {
            $collection->setItems($result);
        }
    }

    /**
     * Optimizes the given filter (collection).
     *
     * @param  FiltersCollectionContract $collection
     * @return FiltersCollectionContract|FilterContract|array<FiltersCollectionContract|FilterContract>|null
     */
    protected function optimizeOrOmit(
        FiltersCollectionContract|FilterContract $filter
    ): FiltersCollectionContract|FilterContract|array|null {
        if (is_a($filter, FilterContract::class)) {
            $operator = $filter->getOperator();

            if ($operator === FilterOperator::EXISTS) {
                $value = $filter->getValue();

                if (!is_null($value)) {
                    $value = $this->optimizeOrOmit($value);

                    if (is_a($value, FilterContract::class)) {
                        $relation = $filter->getAttr()->getName();
                        $value->getAttr()->addFromBeginning($relation);
                        $value->setBoolean($filter->getBoolean());
                        $filter = $value;
                    } else {
                        $filter->setValue($value);
                    }
                }
            }

            return $filter;
        } else {
            $this->combineSameRelationFilters($filter);

            $i = 0;

            while (isset($filter[$i])) {
                $item = $this->optimizeOrOmit($filter[$i]);

                if (is_null($item)) {
                    $filter->splice($i, 1);
                } elseif (is_array($item)) {
                    $filter->splice($i, 1, ...$item);
                    $subCount = count($item);
                    $i += $subCount;
                } else {
                    $filter[$i] = $item;
                    $i++;
                }
            }

            return $this->decomposeIdleCollection($filter);
        }
    }

    /**
     * Decomposes the given filters collection if it has no effect.
     *
     * @param  FiltersCollectionContract $collection
     * @return FiltersCollectionContract|FilterContract|array<FiltersCollectionContract|FilterContract>|null
     */
    protected function decomposeIdleCollection(
        FiltersCollectionContract $collection
    ): FiltersCollectionContract|FilterContract|array|null {
        if ($collection->isEmpty()) {
            return null;
        } elseif ($collection->count() === 1) {
            // If there is only one item, then return that item instead of the collection.
            $item = $collection[0];
            $item->setBoolean($collection->getBoolean());

            return $item;
        } else {
            // If there is at least one boolean OR operator then we can't
            // decompose the collection.
            if ($collection->containsBoolOr()) {
                return $collection;
            }

            // Otherwise, it should be decomposed as it has no effect.
            $collection[0]->setBoolean($collection->getBoolean());

            return $collection->getItems();
        }
    }

    /**
     * Combines filters having the same relation in the given collection.
     *
     * @param  FiltersCollectionContract $collection
     * @return void
     */
    protected function combineSameRelationFilters(FiltersCollectionContract $collection): void
    {
        if ($collection->count() < 2) {
            return;
        }

        $items = $collection->getItems();

        if ($this->isSameRelationFiltersArr($items)) {
            $collection->setItems(
                $this->combineSameRelationFiltersArr(
                    $items,
                    $collection->getBoolean()
                )
            );

            return;
        }

        $chunkStart = 0;
        $chunk = [$items[0]];
        $itemsCount = count($items);

        for ($i = 1; $i < $itemsCount; $i++) {
            $item = $items[$i];

            if ($item->getBoolean() === BooleanOperator::AND) {
                $chunk[] = $item;
            } else {
                $this->combineReplaceFilters($items, $chunk, $chunkStart);
                $countDiff = $itemsCount - count($items);
                $itemsCount -= $countDiff;
                $i -= $countDiff;

                $chunkStart = $i;
                $chunk = [$item];
            }
        }

        $this->combineReplaceFilters($items, $chunk, $chunkStart);
        $collection->setItems(...$items);
    }

    /**
     * Checks whether the given items have the same relation.
     *
     * @param  array $items
     * @return bool
     */
    protected function isSameRelationFiltersArr(array $items): bool
    {
        $relation = null;

        foreach ($items as $item) {
            if (is_a($item, FiltersCollectionContract::class)) {
                if (!$this->isSameRelationFiltersArr($item->getItems())) {
                    return false;
                }

                do {
                    $item = $item[0];
                } while (is_a($item, FiltersCollectionContract::class));
            }

            if ($item->getOperator() === FilterOperator::DOES_NOT_EXIST) {
                return false;
            }

            $itemRel = $item->getAttr()->getNameExceptLastSegment();

            if (!$itemRel && $item->getOperator() === FilterOperator::EXISTS) {
                $itemRel = $item->getAttr()->getNameLastSegment();
            }

            if (is_null($itemRel)) {
                return false;
            } elseif (is_null($relation)) {
                $relation = $itemRel;
            } elseif ($relation !== $itemRel) {
                return false;
            }
        }

        return true;
    }

    /**
     * Combines validated same relation filters.
     *
     * @param  array $items
     * @param  string $boolean
     * @return FilterContract
     */
    protected function combineSameRelationFiltersArr(array $items, string $boolean): FilterContract
    {
        foreach ($items as $i => $item) {
            if (is_a($item, FiltersCollectionContract::class)) {
                $items[$i] = $item = $this->combineSameRelationFiltersArr(
                    $item->getItems(),
                    $item->getBoolean()
                );
            }

            $attr = $item->getAttr();
            $relation = $attr->getNameExceptLastSegment();

            if ($item->getOperator() === FilterOperator::EXISTS) {
                $relation ??= $attr->getNameLastSegment();
                $items[$i] = $item = LaRepo::newFiltersCollection(
                    $item->getBoolean(),
                    ...$item->getValue()
                );
            } else {
                $attr->setName($attr->getNameLastSegment());
            }
        }

        $attr = App::makeWith(DataAttrContract::class, [$relation]);

        return LaRepo::newFilter($attr, FilterOperator::EXISTS, $items, $boolean);
    }

    /**
     * Combines and replaces the given chunk of filters in the filters source.
     *
     * @param  array &$source
     * @param  array $chunk
     * @param  int $offset
     * @return void
     */
    protected function combineReplaceFilters(
        array &$source,
        array $chunk,
        int $offset
    ): void {
        $chunkCount = count($chunk);

        if ($chunkCount > 1 && $this->isSameRelationFiltersArr($chunk)) {
            $combined = $this->combineSameRelationFiltersArr(
                $chunk,
                $chunk[0]->getBoolean()
            );

            array_splice($source, $offset, $chunkCount, $combined);
        }
    }
}
