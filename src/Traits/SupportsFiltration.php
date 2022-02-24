<?php

namespace LaravelRepository\Traits;

use LaravelRepository\Filter;
use LaravelRepository\FilterGroup;
use LaravelRepository\FilterFactory;
use LaravelRepository\FilterOptimizer;
use LaravelRepository\Enums\FilterGroupMode;

trait SupportsFiltration
{
    /**
     * The data filtration params.
     *
     * @var FilterGroup|null
     */
    public ?FilterGroup $filters = null;

    /**
     * Parses data filtration raw string params.
     *
     * @param  string $rawStr
     * @return array|null
     */
    public static function parseFiltrationStr(string $rawStr): ?array
    {
        return json_decode($rawStr, true);
    }

    /**
     * Sets data filtration params from the given raw filters string.
     *
     * @param  string $rawStr
     * @return static
     * @throws \Exception
     */
    public function setFiltersRaw(string $rawStr): static
    {
        $filters = static::parseFiltrationStr($rawStr);

        if (!$filters) {
            throw new \Exception(__('lrepo::exceptions.invalid_filtration_string'));
        }

        foreach ($filters as $i => $item) {
            $filters[$i] = $this->createFilter($item);
        }

        $filters = FilterOptimizer::instance()->optimize($filters);
        $this->filters = FilterGroup::make(null, FilterGroupMode::HAS, false, ...$filters);

        return $this;
    }

    /**
     * Creates a repository filter object from the given associative array.
     *
     * @param  array $data
     * @param  string|null $groupRelation
     * @return Filter|FilterGroup
     */
    protected function createFilter(array $data, ?string $groupRelation = null): Filter|FilterGroup
    {
        $relation = $data['relation'] ?? null;
        $orCond = $data['orCond'] ?? false;

        if (isset($data['items'])) {
            $mode = $data['mode'] ?? FilterGroupMode::HAS;
            $group = FilterGroup::make($relation, $mode, $orCond);
            $itemsGroupRelation = $groupRelation
                ? "$groupRelation.$relation"
                : $relation;

            foreach ($data['items'] as $item) {
                $item = $this->createFilter($item, $itemsGroupRelation);
                $group[] = $item;
            }

            return $group;
        } else {
            $mode = $data['mode'];
            $value = $data['value'] ?? null;

            if (isset($data['attr'])) {
                $prefix = $groupRelation ? "$groupRelation." : '';
                $attr = $prefix . $data['attr'];
                $attr = substr($attr, strlen($prefix));
            } else {
                $attr = null;
            }

            return FilterFactory::instance()->create($mode, $attr, $value, $orCond);
        }
    }
}
