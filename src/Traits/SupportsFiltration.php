<?php

namespace Deluxetech\LaRepo\Traits;

use Illuminate\Support\Facades\App;
use Deluxetech\LaRepo\FilterFactory;
use Deluxetech\LaRepo\Enums\FilterOperator;
use Deluxetech\LaRepo\Contracts\FilterContract;
use Deluxetech\LaRepo\Contracts\FilterOptimizerContract;
use Deluxetech\LaRepo\Contracts\FiltersCollectionContract;
use Deluxetech\LaRepo\Contracts\FiltersCollectionFormatterContract;

trait SupportsFiltration
{
    /**
     * The data filtration params.
     *
     * @var FiltersCollectionContract|null
     */
    protected ?FiltersCollectionContract $filters = null;

    /** @inheritdoc */
    public function getFilters(): ?FiltersCollectionContract
    {
        return $this->filters;
    }

    /** @inheritdoc */
    public function setFiltersRaw(string $rawStr): static
    {
        $dataArr = App::make(FiltersCollectionFormatterContract::class)->parse($rawStr);

        if (!$dataArr) {
            throw new \Exception(__('larepo::exceptions.invalid_filters_string'));
        }

        $filters = App::makeWith(FiltersCollectionContract::class);

        foreach ($dataArr as $filterData) {
            $filter = $this->createFilter($filterData);
            $filters->add($filter);
        }

        App::make(FilterOptimizerContract::class)->optimize($filters);
        $this->setFilters($filters);

        return $this;
    }

    /** @inheritdoc */
    public function setFilters(?FiltersCollectionContract $filters): static
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * Creates a repository filter object from the given associative array.
     *
     * @param  array $data
     * @return FiltersCollectionContract|FilterContract
     */
    protected function createFilter(array $data): FiltersCollectionContract|FilterContract
    {
        $operator = $data['operator'] ?? FilterOperator::AND;

        if (isset($data['items'])) {
            $collection = App::makeWith(FiltersCollectionContract::class, [$operator]);

            foreach ($data['items'] as $item) {
                $item = $this->createFilter($item);
                $collection->add($item);
            }

            return $collection;
        } else {
            $attr = $data['attr'];
            $mode = $data['mode'];
            $value = $data['value'] ?? null;

            if (is_array($value)) {
                $value = $this->createFilter(['items' => $value]);
            }

            return FilterFactory::create($mode, $attr, $value, $operator);
        }
    }
}
