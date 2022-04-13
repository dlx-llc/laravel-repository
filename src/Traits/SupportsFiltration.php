<?php

namespace Deluxetech\LaRepo\Traits;

use Illuminate\Support\Facades\App;
use Deluxetech\LaRepo\Facades\LaRepo;
use Deluxetech\LaRepo\Enums\FilterOperator;
use Deluxetech\LaRepo\Enums\BooleanOperator;
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

        $filters = LaRepo::newFiltersCollection();

        foreach ($dataArr as $filterData) {
            $filter = $this->createFilter($filterData);
            $filters->add($filter);
        }

        $filters = App::make(FilterOptimizerContract::class)->optimize($filters);
        $this->setFilters($filters);

        return $this;
    }

    /** @inheritdoc */
    public function setFilters(?FiltersCollectionContract $filters): static
    {
        $this->filters = $filters;

        return $this;
    }

    /** @inheritdoc */
    public function where(string $attr, mixed $operator, mixed $value = null): static
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = FilterOperator::EQUALS_TO;
        }

        $this->addFilter($attr, $operator, $value, BooleanOperator::AND);

        return $this;
    }

    /** @inheritdoc */
    public function orWhere(string $attr, mixed $operator, mixed $value = null): static
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = FilterOperator::EQUALS_TO;
        }

        $this->addFilter($attr, $operator, $value, BooleanOperator::OR);

        return $this;
    }

    /**
     * Adds a new filter.
     *
     * @param  string $attr
     * @param  string $operator
     * @param  mixed  $value
     * @param  string $boolean
     * @return void
     */
    protected function addFilter(string $attr, string $operator, mixed $value, string $boolean): void
    {
        if (is_null($this->filters)) {
            $this->setFilters(LaRepo::newFiltersCollection());
        }

        $filter = LaRepo::newFilter($attr, $operator, $value, $boolean);
        $this->filters->add($filter);
    }

    /**
     * Creates a repository filter object from the given associative array.
     *
     * @param  array $data
     * @return FiltersCollectionContract|FilterContract
     */
    protected function createFilter(array $data): FiltersCollectionContract|FilterContract
    {
        $boolean = $data['boolean'] ?? BooleanOperator::AND;

        if (isset($data['items'])) {
            $collection = LaRepo::newFiltersCollection($boolean);

            foreach ($data['items'] as $item) {
                $item = $this->createFilter($item);
                $collection->add($item);
            }

            return $collection;
        } else {
            $attr = $data['attr'];
            $operator = $data['operator'];
            $value = $data['value'] ?? null;

            if (!empty($value)) {
                if (
                    $operator === FilterOperator::EXISTS ||
                    $operator === FilterOperator::DOES_NOT_EXIST
                ) {
                    $value = $this->createFilter(['items' => $value]);
                }
            }

            return LaRepo::newFilter($attr, $operator, $value, $boolean);
        }
    }
}
