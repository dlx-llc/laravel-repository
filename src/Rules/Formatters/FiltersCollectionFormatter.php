<?php

declare(strict_types=1);

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
        return json_encode($this->convertFiltersCollectionToArray($collection, false)) ?: '';
    }

    /**
     * @return array<mixed>
     */
    protected function convertFiltersCollectionToArray(
        FiltersCollectionContract $collection,
        bool $withBoolean,
    ): array {
        $items = array_map(function (FilterContract|FiltersCollectionContract $item): array {
            if ($item instanceof FilterContract) {
                return $this->convertFilterToArray($item);
            }

            return $this->convertFiltersCollectionToArray($item, true);
        }, $collection->getItems());

        return $withBoolean ? [
            'boolean' => $collection->getBoolean(),
            'items' => $items,
        ] : $items;
    }

    /**
     * @param FilterContract<mixed> $filter
     * @return array<string,mixed>
     */
    protected function convertFilterToArray(FilterContract $filter): array
    {
        $value = $filter->getValue();

        if (is_object($value) && is_a($value, FiltersCollectionContract::class)) {
            $value = $this->convertFiltersCollectionToArray($value, false);
        }

        return [
            'boolean' => $filter->getBoolean(),
            'attr' => $filter->getAttr()->getName(),
            'operator' => FilterRegistry::getOperator($filter::class),
            'value' => $value,
        ];
    }
}
