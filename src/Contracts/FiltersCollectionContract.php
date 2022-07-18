<?php

namespace Deluxetech\LaRepo\Contracts;

use Iterator;
use Countable;
use ArrayAccess;
use Deluxetech\LaRepo\Enums\BooleanOperator;

interface FiltersCollectionContract extends Iterator, Countable, ArrayAccess
{
    /**
     * Class constructor.
     *
     * @param  string $boolean  The boolean operator by which the filters collection will be combined with others.
     * @param  FiltersCollectionContract|FilterContract ...$items
     */
    public function __construct(
        string $boolean = BooleanOperator::AND,
        FiltersCollectionContract|FilterContract ...$items
    );

    /**
     * Makes a duplicate of the collection object.
     *
     * @return static
     */
    public function clone(): static;

    /**
     * Returns the boolean operator by which the filter will be combined with others.
     *
     * @return string
     */
    public function getBoolean(): string;

    /**
     * Specifies the boolean operator by which the filter will be combined with others.
     *
     * @param  string $boolean
     * @return static
     */
    public function setBoolean(string $boolean): static;

    /**
     * Returns the collection items.
     *
     * @return array<FiltersCollectionContract|FilterContract>
     */
    public function getItems(): array;

    /**
     * Checks if there is at least one item in the collection that has "or" boolean.
     * The first item's boolean operator doesn't matter.
     *
     * @return bool
     */
    public function containsBoolOr(): bool;

    /**
     * Checks if all the items in the collection have the same boolean operator.
     * The first item's boolean operator doesn't matter.
     *
     * @return bool
     */
    public function checkBooleansAreSame(): bool;

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
