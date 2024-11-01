<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo;

use Illuminate\Support\Facades\App;
use Deluxetech\LaRepo\Facades\LaRepo;
use Deluxetech\LaRepo\Enums\FilterOperator;
use Deluxetech\LaRepo\Enums\BooleanOperator;
use Deluxetech\LaRepo\Contracts\FilterContract;
use Deluxetech\LaRepo\Contracts\SortingContract;
use Deluxetech\LaRepo\Contracts\CriteriaContract;
use Deluxetech\LaRepo\Contracts\TextSearchContract;
use Deluxetech\LaRepo\Contracts\FilterOptimizerContract;
use Deluxetech\LaRepo\Contracts\SortingFormatterContract;
use Deluxetech\LaRepo\Contracts\FiltersCollectionContract;
use Deluxetech\LaRepo\Contracts\TextSearchFormatterContract;
use Deluxetech\LaRepo\Exceptions\InvalidFiltersStringException;
use Deluxetech\LaRepo\Exceptions\InvalidSortingStringException;
use Deluxetech\LaRepo\Exceptions\InvalidTextSearchStringException;
use Deluxetech\LaRepo\Contracts\FiltersCollectionFormatterContract;

class Criteria implements CriteriaContract
{
    /**
     * @var array<string>
     */
    protected array $attributes = [];

    /**
     * @var array<string,?CriteriaContract>
     */
    protected array $relations = [];

    /**
     * @var array<string,?CriteriaContract>
     */
    protected array $relationCounts = [];

    protected ?FiltersCollectionContract $filters = null;
    protected ?SortingContract $sorting = null;
    protected ?TextSearchContract $textSearch = null;

    public function setAttributes(string ...$attributes): static
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function setRelations(array $relations): static
    {
        foreach ($relations as $key => $value) {
            if (is_int($key) && is_string($value)) {
                $this->addRelation($value);
            } elseif (is_string($key) && !is_string($value)) {
                $this->addRelation($key, $value);
            }
        }

        return $this;
    }

    public function addRelation(string $relation, ?CriteriaContract $criteria = null): static
    {
        $this->relations[$relation] = $criteria;

        return $this;
    }

    /**
     * @return array<string,?CriteriaContract>
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    public function setRelationCounts(array $counts): static
    {
        foreach ($counts as $key => $value) {
            if (is_int($key) && is_string($value)) {
                $this->addRelationCount($value);
            } elseif (is_string($key) && !is_string($value)) {
                $this->addRelationCount($key, $value);
            }
        }

        return $this;
    }

    public function addRelationCount(string $relation, ?CriteriaContract $criteria = null): static
    {
        $this->relationCounts[$relation] = $criteria;

        return $this;
    }

    /**
     * @return array<string,?CriteriaContract>
     */
    public function getRelationCounts(): array
    {
        return $this->relationCounts;
    }

    public function getFilters(): ?FiltersCollectionContract
    {
        return $this->filters;
    }

    /**
     * @throws InvalidFiltersStringException
     */
    public function setFiltersRaw(string $rawStr): static
    {
        $dataArr = App::make(FiltersCollectionFormatterContract::class)->parse($rawStr);

        if (!$dataArr) {
            throw new InvalidFiltersStringException();
        }

        $filters = LaRepo::newFiltersCollection();

        foreach ($dataArr as $filterData) {
            $filter = $this->createFilter($filterData);
            $filters->add($filter);
        }

        App::make(FilterOptimizerContract::class)->optimize($filters);
        $this->setFilters($filters);

        return $this;
    }

    public function setFilters(?FiltersCollectionContract $filters): static
    {
        $this->filters = $filters;

        return $this;
    }

    public function where(string $attr, mixed $operator, mixed $value = null): static
    {
        [$attr, $operator, $value] = $this->prepareWhereArgs(...func_get_args());
        $this->addFilter($attr, $operator, $value, BooleanOperator::AND);

        return $this;
    }

    public function orWhere(string $attr, mixed $operator, mixed $value = null): static
    {
        [$attr, $operator, $value] = $this->prepareWhereArgs(...func_get_args());
        $this->addFilter($attr, $operator, $value, BooleanOperator::OR);

        return $this;
    }

    public function getSorting(): ?SortingContract
    {
        return $this->sorting;
    }

    /**
     * @throws InvalidSortingStringException
     */
    public function setSortingRaw(string $rawStr): static
    {
        $params = App::make(SortingFormatterContract::class)->parse($rawStr);

        if (!$params) {
            throw new InvalidSortingStringException();
        }

        $sorting = LaRepo::newSorting($params[0], $params[1]);
        $this->setSorting($sorting);

        return $this;
    }

    public function setSorting(?SortingContract $sorting): static
    {
        $this->sorting = $sorting;

        return $this;
    }

    public function getTextSearch(): ?TextSearchContract
    {
        return $this->textSearch;
    }

    /**
     * @throws InvalidTextSearchStringException
     */
    public function setTextSearchRaw(string $rawStr): static
    {
        $params = App::make(TextSearchFormatterContract::class)->parse($rawStr);

        if (!$params) {
            throw new InvalidTextSearchStringException();
        }

        $textSearch = LaRepo::newTextSearch($params[0], ...$params[1]);
        $this->setTextSearch($textSearch);

        return $this;
    }

    public function setTextSearch(?TextSearchContract $textSearch): static
    {
        $this->textSearch = $textSearch;

        return $this;
    }

    public function merge(CriteriaContract $criteria): static
    {
        if ($attributes = $criteria->getAttributes()) {
            $attributes = array_unique([...$this->getAttributes(), ...$attributes]);
            $this->setAttributes(...$attributes);
        }

        if ($relations = $criteria->getRelations()) {
            foreach ($relations as $relation => $relCriteria) {
                $this->addRelation($relation, $relCriteria);
            }
        }

        if ($counts = $criteria->getRelationCounts()) {
            foreach ($counts as $relation => $relCriteria) {
                $this->addRelationCount($relation, $relCriteria);
            }
        }

        if ($sorting = $criteria->getSorting()) {
            $this->setSorting($sorting);
        }

        if ($textSearch = $criteria->getTextSearch()) {
            $this->setTextSearch($textSearch);
        }

        if ($filters = $criteria->getFilters()) {
            if (is_null($this->filters)) {
                $this->filters = $filters->clone();
            } else {
                $this->filters->add($filters);
            }
        }

        return $this;
    }

    public function clone(): static
    {
        $clone = new static();
        $clone->setAttributes(...$this->getAttributes());
        $clone->setRelations($this->getRelations());
        $clone->setRelationCounts($this->getRelationCounts());
        $clone->setSorting($this->getSorting());
        $clone->setTextSearch($this->getTextSearch());
        $clone->setFilters($this->getFilters()?->clone());

        return $clone;
    }

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
     * @param array<string,mixed> $data
     * @return FiltersCollectionContract|FilterContract<mixed>
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
        }

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

    /**
     * Prepares (or)Where method arguments.
     *
     * @return array{0:string,1:mixed,2:mixed}
     */
    protected function prepareWhereArgs(string $attr, mixed $operator, mixed $value = null): array
    {
        if (
            func_num_args() === 2 &&
            $operator !== FilterOperator::IS_NULL &&
            $operator !== FilterOperator::IS_NOT_NULL &&
            $operator !== FilterOperator::EXISTS &&
            $operator !== FilterOperator::DOES_NOT_EXIST
        ) {
            $value = $operator;
            $operator = FilterOperator::EQUALS_TO;
        }

        return [$attr, $operator, $value];
    }
}
