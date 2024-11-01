<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo;

use OutOfBoundsException;
use UnexpectedValueException;
use Deluxetech\LaRepo\Enums\BooleanOperator;
use Deluxetech\LaRepo\Contracts\FilterContract;
use Deluxetech\LaRepo\Contracts\FiltersCollectionContract;

class FiltersCollection implements FiltersCollectionContract
{
    /**
     * The iterator's cursor.
     */
    protected int $cursor = 0;

    /**
     * @var array<FiltersCollectionContract|FilterContract>
     */
    protected array $items;

    public function __construct(
        protected string $boolean = BooleanOperator::AND,
        FiltersCollectionContract|FilterContract ...$items,
    ) {
        $this->items = $items;
    }

    public function clone(): static
    {
        $clone = new static();
        $clone->setItems(...$this->getItems());
        $clone->setBoolean($this->getBoolean());

        return $clone;
    }

    public function getBoolean(): string
    {
        return $this->boolean;
    }

    public function setBoolean(string $boolean): static
    {
        $this->boolean = $boolean;

        return $this;
    }

    public function containsBoolOr(): bool
    {
        $count = count($this->items);

        if ($count < 2) {
            return false;
        }

        for ($i = 1; $i < $count; $i++) {
            if ($this->items[$i]->getBoolean() === BooleanOperator::OR) {
                return true;
            }
        }

        return false;
    }

    public function checkBooleansAreSame(): bool
    {
        $count = count($this->items);

        if ($count < 3) {
            return true;
        }

        $b1 = $this->items[1]->getBoolean();

        for ($i = 2; $i < $count; $i++) {
            $b2 = $this->items[$i]->getBoolean();

            if ($b1 !== $b2) {
                return false;
            }

            $b1 = $b2;
        }

        return true;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function isNotEmpty(): bool
    {
        return !empty($this->items);
    }

    public function setItems(FiltersCollectionContract|FilterContract ...$items): static
    {
        $this->items = $items;
        $this->rewind();

        return $this;
    }

    public function pop(): FiltersCollectionContract|FilterContract|null
    {
        return array_pop($this->items);
    }

    public function add(FiltersCollectionContract|FilterContract $item): static
    {
        $this->items[] = $item;

        return $this;
    }

    public function splice(
        int $offset,
        int $length,
        FiltersCollectionContract|FilterContract ...$replacement
    ): static {
        array_splice($this->items, $offset, $length, $replacement);

        return $this;
    }

    public function rewind(): void
    {
        $this->cursor = 0;
    }

    public function current(): FiltersCollectionContract|FilterContract|null
    {
        return $this->items[$this->cursor] ?? null;
    }

    public function key(): int
    {
        return $this->cursor;
    }

    public function next(): void
    {
        ++$this->cursor;
    }

    public function valid(): bool
    {
        return isset($this->items[$this->cursor]);
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @throws OutOfBoundsException
     * @throws UnexpectedValueException
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } elseif (!is_int($offset)) {
            /** @var string $message */
            $message = __('larepo::exceptions.illegal_filters_collection_offset');

            throw new OutOfBoundsException($message);
        } elseif (!is_object($value) || !is_a($value, FilterContract::class) && !is_a($value, static::class)) {
            /** @var string $message */
            $message = __('larepo::exceptions.illegal_filters_collection_item');

            throw new UnexpectedValueException($message);
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset] ?? null;
    }
}
