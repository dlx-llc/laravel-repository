<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Contracts;

use Iterator;
use Countable;
use ArrayAccess;
use Deluxetech\LaRepo\Enums\BooleanOperator;

/**
 * @extends Iterator<int,FilterContract|FiltersCollectionContract>
 * @extends ArrayAccess<int,FilterContract|FiltersCollectionContract>
 */
interface FiltersCollectionContract extends Iterator, Countable, ArrayAccess
{
    /**
     * @param FiltersCollectionContract|FilterContract<mixed> ...$items
     */
    public function __construct(
        string $boolean = BooleanOperator::AND,
        FiltersCollectionContract|FilterContract ...$items,
    );

    public function clone(): static;

    /**
     * Returns the boolean operator by which the filter will be combined with others.
     */
    public function getBoolean(): string;

    /**
     * Specifies the boolean operator by which the filter will be combined with others.
     */
    public function setBoolean(string $boolean): static;

    /**
     * Returns the collection items.
     *
     * @return array<FiltersCollectionContract|FilterContract<mixed>>
     */
    public function getItems(): array;

    /**
     * Checks if there is at least one item in the collection that has "or" boolean.
     * The first item's boolean operator doesn't matter.
     */
    public function containsBoolOr(): bool;

    /**
     * Checks if all the items in the collection have the same boolean operator.
     * The first item's boolean operator doesn't matter.
     */
    public function checkBooleansAreSame(): bool;

    public function isEmpty(): bool;

    public function isNotEmpty(): bool;

    /**
     * @param FiltersCollectionContract|FilterContract<mixed> ...$items
     */
    public function setItems(FiltersCollectionContract|FilterContract ...$items): static;

    /**
     * Returns the last item of the collection and removes it.
     *
     * @return FiltersCollectionContract|FilterContract<mixed>|null
     */
    public function pop(): FiltersCollectionContract|FilterContract|null;

    /**
     * Adds an item at the end of the collection.
     *
     * @param FiltersCollectionContract|FilterContract<mixed> $item
     */
    public function add(FiltersCollectionContract|FilterContract $item): static;

    /**
     * Replaces a portion of items from the collection.
     *
     * @param FiltersCollectionContract|FilterContract<mixed> ...$replacement
     */
    public function splice(
        int $offset,
        int $length,
        FiltersCollectionContract|FilterContract ...$replacement,
    ): static;
}
