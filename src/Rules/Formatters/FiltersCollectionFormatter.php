<?php

namespace LaravelRepository\Rules\Formatters;

use LaravelRepository\FilterFactory;
use LaravelRepository\Contracts\FilterContract;
use LaravelRepository\Contracts\FiltersCollectionContract;
use LaravelRepository\Contracts\FiltersCollectionFormatterContract;

class FiltersCollectionFormatter implements FiltersCollectionFormatterContract
{
    /** @inheritdoc */
    public function parse(string $str): ?array
    {
        return json_decode($str, true);
    }

    /** @inheritdoc */
    public function stringify(FiltersCollectionContract $collection): string
    {
        return json_encode([
            'operator' => $collection->getOperator(),
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
            'operator' => $filter->getOperator(),
            'attr' => $filter->getAttr()->getNameWithRelation(),
            'mode' => FilterFactory::getMode(get_class($filter)),
            'value' => $value,
        ]) ?: '';
    }
}
