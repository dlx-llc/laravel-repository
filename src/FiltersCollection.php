<?php

namespace LaravelRepository;

use LaravelRepository\Enums\FilterOperator;
use LaravelRepository\Contracts\FilterContract;
use LaravelRepository\Contracts\FiltersCollectionContract;

class FiltersCollection implements FiltersCollectionContract
{
    /**
     * The iterator's cursor.
     *
     * @var int
     */
    protected int $cursor = 0;

    /**
     * The collection items.
     *
     * @var array<FiltersCollectionContract|FilterContract>
     */
    protected array $items;

    /** {@inheritdoc} */
    public function __construct(
        protected string $operator = FilterOperator::AND,
        FiltersCollectionContract|FilterContract ...$items
    ) {
        $this->items = $items;
    }

    /** {@inheritdoc} */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /** {@inheritdoc} */
    public function setOperator(string $operator): static
    {
        $this->operator = $operator;

        return $this;
    }

    /** {@inheritdoc} */
    public function getItems(): array
    {
        return $this->items;
    }

    /** {@inheritdoc} */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /** {@inheritdoc} */
    public function isNotEmpty(): bool
    {
        return !empty($this->items);
    }

    /** {@inheritdoc} */
    public function setItems(FiltersCollectionContract|FilterContract ...$items): static
    {
        $this->items = $items;
        $this->rewind();

        return $this;
    }

    /** {@inheritdoc} */
    public function pop(): FiltersCollectionContract|FilterContract|null
    {
        return array_pop($this->items);
    }

    /** {@inheritdoc} */
    public function add(FiltersCollectionContract|FilterContract $item): static
    {
        $this->items[] = $item;

        return $this;
    }

    /** {@inheritdoc} */
    public function splice(
        int $offset,
        int $length,
        FiltersCollectionContract|FilterContract ...$replacement
    ): static {
        array_splice($this->items, $offset, $length, $replacement);

        return $this;
    }

    /** {@inheritdoc} */
    public function rewind(): void
    {
        $this->cursor = 0;
    }

    /** {@inheritdoc} */
    public function current(): FiltersCollectionContract|FilterContract|null
    {
        return $this->items[$this->cursor] ?? null;
    }

    /** {@inheritdoc} */
    public function key(): int
    {
        return $this->cursor;
    }

    /** {@inheritdoc} */
    public function next(): void
    {
        ++$this->cursor;
    }

    /** {@inheritdoc} */
    public function valid(): bool
    {
        return isset($this->items[$this->cursor]);
    }

    /** {@inheritdoc} */
    public function count(): int
    {
        return count($this->items);
    }

    /** {@inheritdoc} */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } elseif (!is_int($offset)) {
            throw new \Exception(__('lrepo::exceptions.illegal_filters_collection_offset'));
        } elseif (!is_object($value) || !is_a($value, FilterContract::class) && !is_a($value, static::class)) {
            throw new \Exception(__('lrepo::exceptions.illegal_filters_collection_item'));
        } else {
            $this->items[$offset] = $value;
        }
    }

    /** {@inheritdoc} */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    /** {@inheritdoc} */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }

    /** {@inheritdoc} */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset] ?? null;
    }
}
