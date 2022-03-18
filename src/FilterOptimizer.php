<?php

namespace Deluxetech\LaRepo;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Deluxetech\LaRepo\Enums\FilterOperator;
use Deluxetech\LaRepo\Contracts\FilterContract;
use Deluxetech\LaRepo\Contracts\DataAttrContract;
use Deluxetech\LaRepo\Filters\RelationExistsFilter;
use Deluxetech\LaRepo\Contracts\FilterOptimizerContract;
use Deluxetech\LaRepo\Filters\RelationDoesNotExistFilter;
use Deluxetech\LaRepo\Contracts\FiltersCollectionContract;

/**
 * @todo Remove filter optimizer and create an eloquent query optimizer instead.
 */
class FilterOptimizer implements FilterOptimizerContract
{
    /**
     * Removes meaningless collections and groups same relation filters if possible.
     *
     * @param  FiltersCollectionContract $collection
     * @return void
     */
    public function optimize(FiltersCollectionContract $collection): void
    {
        $this->combineSameRelationFilters($collection);

        $items = $collection->getItems();

        foreach ($items as $i => $item) {
            if (is_a($item, FiltersCollection::class)) {
                $items[$i] = $this->decomposeIdleCollection($item);
            }
        }

        $collection->setItems(...Arr::flatten($items));
    }

    /**
     * Decomposes the given filters collection if it has no effect.
     *
     * @param  FiltersCollectionContract $collection
     * @return array<FiltersCollectionContract|FilterContract>
     */
    protected function decomposeIdleCollection(FiltersCollectionContract $collection): array
    {
        if ($collection->isEmpty()) {
            return [];
        }

        $items = [];

        foreach ($collection as $i => $item) {
            if (is_a($item, FiltersCollection::class)) {
                // Decompose meaningless nested collections recursively.
                $items = [...$items, ...$this->decomposeIdleCollection($item)];
            } else {
                $items[] = $item;
            }
        }

        $collection->setItems(...$items);

        if ($collection->count() === 1) {
            // If there is only one item, then return that item instead of the collection.
            $item = $collection[0];
            $item->setOperator($collection->getOperator());

            return [$item];
        } else {
            // If there is at least one logical OR operator then we shouldn't
            // decompose the collection.
            foreach ($collection as $i => $item) {
                if ($i && $item->getOperator() === FilterOperator::OR) {
                    return [$collection];
                }
            }

            // Otherwise, it should be decomposed as it has no effect.
            $collection[0]->setOperator($collection->getOperator());

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
                    $collection->getOperator()
                )
            );

            return;
        }

        $chunkStart = 0;
        $chunk = [$items[0]];
        $itemsCount = count($items);

        for ($i = 1; $i < $itemsCount; $i++) {
            $item = $items[$i];

            if ($item->getOperator() === FilterOperator::AND) {
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

            if (is_a($item, RelationDoesNotExistFilter::class)) {
                return false;
            }

            $itemRel = $item->getAttr()->getNameExceptLastSegment();

            if (!$itemRel && is_a($item, RelationExistsFilter::class)) {
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
     * @param  string $operator
     * @return FilterContract
     */
    protected function combineSameRelationFiltersArr(array $items, string $operator): FilterContract
    {
        foreach ($items as $i => $item) {
            if (is_a($item, FiltersCollectionContract::class)) {
                $items[$i] = $item = $this->combineSameRelationFiltersArr(
                    $item->getItems(),
                    $item->getOperator()
                );
            }

            $attr = $item->getAttr();
            $relation = $attr->getNameExceptLastSegment();

            if (is_a($item, RelationExistsFilter::class)) {
                $relation ??= $attr->getNameLastSegment();
                $items[$i] = $item = App::makeWith(FiltersCollectionContract::class, [
                    $item->getOperator(),
                    ...$item->getValue(),
                ]);
            } else {
                $attr->setName($attr->getNameLastSegment());
            }
        }

        $attr = App::makeWith(DataAttrContract::class, [$relation]);

        return new RelationExistsFilter($attr, $items, $operator);
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
                $chunk[0]->getOperator()
            );

            array_splice($source, $offset, $chunkCount, $combined);
        }
    }
}
