<?php

namespace LaravelRepository\Contracts;

use Iterator;
use Countable;
use ArrayAccess;
use LaravelRepository\Enums\FilterOperator;

interface FiltersCollectionContract extends Iterator, Countable, ArrayAccess
{
    /**
     * Class constructor.
     *
     * @param  string $operator  The logical operator by which the filters collection will be combined with others.
     * @param  FiltersCollectionContract|FilterContract ...$items
     * @return void
     */
    public function __construct(
        string $operator = FilterOperator::AND,
        FiltersCollectionContract|FilterContract ...$items
    );

    /**
     * Returns the logical operator by which the filter will be combined with others.
     *
     * @return string
     */
    public function getOperator(): string;

    /**
     * Specifies the logical operator by which the filter will be combined with others.
     *
     * @param  string $operator
     * @return static
     */
    public function setOperator(string $operator): static;

    /**
     * Returns the collection items.
     *
     * @return array<FiltersCollectionContract|FilterContract>
     */
    public function getItems(): array;

    /**
     * Checks if the collection is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * Checks if the collection is not empty.
     *
     * @return bool
     */
    public function isNotEmpty(): bool;

    /**
     * Set the collection items.
     *
     * @param  FiltersCollection|FilterContract ...$items
     * @return static
     */
    public function setItems(FiltersCollectionContract|FilterContract ...$items): static;

    /**
     * Pops the last item of the collection.
     *
     * @return FiltersCollectionContract|FilterContract|null
     */
    public function pop(): FiltersCollectionContract|FilterContract|null;

    /**
     * Adds an item in the collection.
     *
     * @param  FiltersCollectionContract|FilterContract $item
     * @return static
     */
    public function add(FiltersCollectionContract|FilterContract $item): static;

    /**
     * Removes a portion of the items and replaces it.
     *
     * @param  int $offset
     * @param  int $length
     * @param  FiltersCollectionContract|FilterContract ...$replacement
     * @return static
     */
    public function splice(
        int $offset,
        int $length,
        FiltersCollectionContract|FilterContract ...$replacement
    );
}
