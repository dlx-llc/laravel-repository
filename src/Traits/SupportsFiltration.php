<?php

namespace LaravelRepository\Traits;

use Illuminate\Support\Facades\App;
use LaravelRepository\FilterFactory;
use LaravelRepository\Enums\FilterOperator;
use LaravelRepository\Contracts\FilterContract;
use LaravelRepository\Contracts\FilterOptimizerContract;
use LaravelRepository\Contracts\FiltersCollectionContract;

trait SupportsFiltration
{
    /**
     * The data filtration params.
     *
     * @var FiltersCollectionContract|null
     */
    public ?FiltersCollectionContract $filters = null;

    /**
     * Sets data filtration params from the given raw filters string.
     *
     * @param  string $rawStr
     * @return static
     * @throws \Exception
     */
    public function setFiltersRaw(string $rawStr): static
    {
        $filters = App::make(FiltersCollectionParserContract::class)->parse($rawStr);

        if (!$filters) {
            throw new \Exception(__('lrepo::exceptions.invalid_filtration_string'));
        }

        $this->filters = App::makeWith(FiltersCollectionContract::class);

        foreach ($filters as $filter) {
            $filter = $this->createFilter($filter);
            $this->filters->add($filter);
        }

        App::make(FilterOptimizerContract::class)->optimize($this->filters);

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

            return FilterFactory::create($mode, $attr, $value, $operator);
        }
    }
}
