<?php

namespace LaravelRepository;

use Illuminate\Support\Arr;
use LaravelRepository\Filter;
use LaravelRepository\FilterGroup;
use LaravelRepository\Enums\FilterGroupMode;

/**
 * Can be used to optimize search criteria filters for further application in a DB query.
 */
class FilterOptimizer
{
    /**
     * The single instance of this class.
     *
     * @var static|null
     */
    private static ?FilterOptimizer $instance = null;

    /**
     * Returns an instance of this class.
     *
     * @return static
     */
    public static function instance(): static
    {
        if (!static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Creates an instance of this class.
     *
     * @return void
     */
    private function __construct()
    {
        //
    }

    /**
     * Optimizes the given array of filters and filter groups.
     *
     * @param  array<Filter|FilterGroup> $items
     * @return array<Filter|FilterGroup>
     */
    public function optimize(array $items): array
    {
        foreach ($items as $i => $item) {
            if (is_a($item, FilterGroup::class)) {
                $item = $this->breakDownMultilayerRelations($item);
                $item = $this->spreadOutGroupIfUnnecessary($item, false);

                if (is_a($item, FilterGroup::class)) {
                    $items[$i] = $this->setGroupRelation($item);
                }
            } else {
                $items[$i] = $this->breakDownMultilayerRelations($item);
            }
        }

        $items = Arr::flatten($items);
        $items = $this->joinSameRelationFilters($items);

        foreach ($items as $i => $item) {
            if (is_a($item, FilterGroup::class)) {
                $items[$i] = $this->spreadOutGroupIfUnnecessary($item, true);
            }
        }

        return Arr::flatten($items);
    }

    /**
     * Checks whether the group contains only items of the same relation.
     * If so, sets that relation on the group.
     *
     * @param  FilterGroup $group
     * @return FilterGroup
     */
    protected function setGroupRelation(FilterGroup $group): FilterGroup
    {
        if (empty($group)) {
            return $group;
        }

        foreach ($group as $i => $item) {
            if (is_a($item, FilterGroup::class)) {
                $group[$i] = $this->setGroupRelation($item);
            }
        }

        if ($group->relation) {
            return $group;
        }

        $relation = $group[0]->relation;

        foreach ($group as $item) {
            if (!$item->relation || $item->relation !== $relation) {
                return $group;
            }
        }

        $group->relation = $relation;

        foreach ($group as $item) {
            $item->relation = null;
        }

        return $group;
    }

    /**
     * Turns the filter group into sequential filters if all the included items
     * are connected by the "and" condition and there is no relation on the group.
     * In case if the group contains only one item, that only item will be returned.
     *
     * @param  array<Filter|FilterGroup>|FilterGroup $group
     * @param  bool $joinMultilayerRelations
     * @return array<Filter|FilterGroup>|Filter|FilterGroup
     */
    protected function spreadOutGroupIfUnnecessary(
        FilterGroup $group,
        bool $joinMultilayerRelations
    ): array|Filter|FilterGroup {
        if (empty($group)) {
            return [];
        }

        foreach ($group as $i => $item) {
            if (is_a($item, FilterGroup::class)) {
                $group[$i] = $this->spreadOutGroupIfUnnecessary($item, $joinMultilayerRelations);
            }
        }

        if ($group->count() === 1) {
            if (
                !$group->relation ||
                $group->mode === FilterGroupMode::HAS && $joinMultilayerRelations
            ) {
                $item = $group[0];
                $item->orCond = $group->orCond;
                $item->relation = trim("{$group->relation}.{$item->relation}", '.') ?: null;

                return $item;
            } else {
                return $group;
            }
        } elseif ($group->relation) {
            return $group;
        } else {
            foreach ($group as $i => $item) {
                if ($i && $item->orCond) {
                    return $group;
                }
            }

            $group[0]->orCond = $group->orCond;

            return $group->all();
        }
    }

    /**
     * Breaks down the filter or the filters group if it has a multilayer relation.
     * The item will be transformed to inbuilt filter groups with single layer relations.
     *
     * @param  Filter|FilterGroup $item
     * @return Filter|FilterGroup
     */
    protected function breakDownMultilayerRelations(Filter|FilterGroup $item): Filter|FilterGroup
    {
        $isFilterGroup = is_a($item, FilterGroup::class);

        if ($isFilterGroup) {
            foreach ($item as $i => $groupItem) {
                $item[$i] = $this->breakDownMultilayerRelations($groupItem);
            }
        }

        if (!is_null($item->relation) && str_contains($item->relation, '.')) {
            $relations = explode('.', $item->relation);
            $item->relation = array_pop($relations);

            if ($isFilterGroup) {
                $mode = $item->mode;
                $item->mode = FilterGroupMode::HAS;
            } else {
                $mode = FilterGroupMode::HAS;
            }

            for ($j = count($relations) - 1; $j >= 0; $j--) {
                $item = FilterGroup::make(
                    $relations[$j],
                    FilterGroupMode::HAS,
                    $item->orCond,
                    $item
                );
            }

            $item->mode = $mode;
        }

        return $item;
    }

    /**
     * Joins filters and filter groups with the same relation where it's possible.
     *
     * @param  array<Filter|FilterGroup> $items
     * @return array<Filter|FilterGroup>
     */
    protected function joinSameRelationFilters(array $items): array
    {
        if (count($items) < 2) {
            return $items;
        }

        $joinable = [];
        $relation = null;
        $relationGroup = [];
        $skipUntilOrCond = false;

        foreach ($items as $i => $item) {
            if ($item->orCond) {
                if ($relationGroup && $relation) {
                    $joinable[$relation] ??= [];
                    $joinable[$relation] = [...$joinable[$relation], ...$relationGroup];
                }

                if ($relation = $item->relation) {
                    $relationGroup = [$i];
                    $skipUntilOrCond = false;
                } else {
                    $relationGroup = [];
                    $skipUntilOrCond = true;
                }
            } else {
                if ($skipUntilOrCond) {
                    continue;
                } elseif (!$relation && $item->relation) {
                    $relation = $item->relation;
                    $relationGroup = [$i];
                    $skipUntilOrCond = false;
                } elseif (!$item->relation || $item->relation !== $relation) {
                    $relation = null;
                    $relationGroup = [];
                    $skipUntilOrCond = true;
                } else {
                    $relationGroup[] = $i;
                    $skipUntilOrCond = false;
                }
            }
        }

        if ($relationGroup && $relation) {
            $joinable[$relation] ??= [];
            $joinable[$relation] = [...$joinable[$relation], ...$relationGroup];
        }

        foreach ($joinable as $relation => $indexes) {
            $relationItems = [];

            foreach ($indexes as $i) {
                $relationItems[] = $items[$i];
            }

            $firstPos = array_shift($indexes);
            $newGroup = $this->groupItems($relation, ...$relationItems);
            $items[$firstPos] = $newGroup;

            foreach ($indexes as $i) {
                unset($items[$i]);
            }
        }

        foreach ($items as $item) {
            if (is_a($item, FilterGroup::class)) {
                $groupItems = $this->joinSameRelationFilters($item->all());
                $item->set(...$groupItems);
            }
        }

        return array_values($items);
    }

    /**
     * Groups the given filters and filter groups in one filter group with the given relation.
     *
     * @param  string|null $relation
     * @param  Filter|FilterGroup ...$filters
     * @return Filter|FilterGroup|null
     */
    protected function groupItems(?string $relation, Filter|FilterGroup ...$filters): Filter|FilterGroup|null
    {
        $count = count($filters);

        if ($count === 1) {
            return $filters[0];
        } elseif ($count > 1) {
            if ($relation) {
                foreach ($filters as $i => $filter) {
                    $filter->relation = null;

                    if (is_a($filter, FilterGroup::class)) {
                        $filters[$i] = $this->spreadOutGroupIfUnnecessary($filter, false);
                    }
                }

                $filters = Arr::flatten($filters);
            }

            return FilterGroup::make(
                $relation,
                FilterGroupMode::HAS,
                $filters[0]->orCond,
                ...$filters
            );
        } else {
            return null;
        }
    }
}
