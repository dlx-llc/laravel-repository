<?php

namespace Deluxetech\LaRepo\Rules\Formatters;

use Deluxetech\LaRepo\FilterRegistry;
use Deluxetech\LaRepo\Contracts\FilterContract;
use Deluxetech\LaRepo\Contracts\FiltersCollectionContract;
use Deluxetech\LaRepo\Contracts\FiltersCollectionFormatterContract;

class FiltersCollectionFormatter implements FiltersCollectionFormatterContract
{
    public function parse(string $str): ?array
    {
        return json_decode($str, true);
    }

    public function stringify(FiltersCollectionContract $collection): string
    {
        return json_encode([
            'boolean' => $collection->getBoolean(),
            'items' => $this->stringifyArray($collection->getItems()),
        ]) ?: '';
    }

    /**
     * Converts a filters array to string.
     *
     * @param  array $items
     * @return string
     */
    protected function stringifyArray(array $items): string
    {
        $arr = [];

        foreach ($items as $item) {
            $arr[] = match (get_class($item)) {
                FilterContract::class => $this->stringifyFilterObject($item),
                FiltersCollectionContract::class => $this->stringify($item),
            };
        }

        return json_encode($arr) ?: '';
    }

    /**
     * Converts a filter object to string.
     *
     * @param  FilterContract $filter
     * @return string
     */
    protected function stringifyFilterObject(FilterContract $filter): string
    {
        $value = $filter->getValue();

        if (is_object($value) && is_a($value, FiltersCollectionContract::class)) {
            $value = $this->stringifyArray($value->getItems());
        }

        return json_encode([
            'boolean' => $filter->getBoolean(),
            'attr' => $filter->getAttr()->getName(),
            'operator' => FilterRegistry::getOperator(get_class($filter)),
            'value' => $value,
        ]) ?: '';
    }
}
