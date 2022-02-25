<?php

namespace LaravelRepository;

use Iterator;
use Countable;
use ArrayAccess;

/**
 * Example:
 * {
 *   "orCond": false,
 *   "relation": "creator",
 *   "mode": "has",
 *   "items": [
 *     {
 *       "attr": "first_name",
 *       "mode": "=",
 *       "value": "John"
 *     },
 *     {
 *       "attr": "role.name",
 *       "mode": "like",
 *       "value": "admin"
 *     }
 *   ]
 * }
 */
class FilterGroup implements Iterator, Countable, ArrayAccess
{
    /**
     * Indicates the conditional operator which will be used to apply the filter on the query.
     *
     * @var bool
     */
    public bool $orCond = false;

    /**
     * If specified, the items in the group will be applied to the given relation.
     *
     * @var string|null
     */
    public ?string $relation = null;

    /**
     * The filter mode.
     *
     * @var string
     */
    public string $mode;

    /**
     * @var array<Filter|FilterGroup>
     */
    protected array $items;

    /**
     * The iterator's cursor.
     *
     * @var int
     */
    protected int $cursor = 0;

    /**
     * Makes a new instance of this class.
     *
     * @param  string|null $relation
     * @param  string $mode
     * @param  bool $orCond
     * @param  Filter|FilterGroup ...$items
     * @return static
     */
    public static function make(
        ?string $relation,
        string $mode,
        bool $orCond,
        Filter|FilterGroup ...$items
    ): static {
        return new static($relation, $mode, $orCond, ...$items);
    }

    /**
     * Constructor.
     *
     * @param  string|null $relation
     * @param  string $mode
     * @param  bool $orCond
     * @param  Filter|FilterGroup ...$items
     * @return void
     */
    public function __construct(
        ?string $relation,
        string $mode,
        bool $orCond,
        Filter|FilterGroup ...$items
    ) {
        $this->relation = $relation;
        $this->mode = $mode;
        $this->orCond = $orCond;
        $this->items = $items;
    }

    /**
     * Set the group filters.
     *
     * @param  Filter|FilterGroup ...$items
     * @return static
     */
    public function set(Filter|FilterGroup ...$items): static
    {
        $this->items = $items;
        $this->rewind();

        return $this;
    }

    /**
     * Returns all the group items.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->items;
    }

    /** {@inheritdoc} */
    public function rewind(): void
    {
        $this->cursor = 0;
    }

    /** {@inheritdoc} */
    public function current(): Filter|FilterGroup|null
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
        } elseif (!is_object($value) || !is_a($value, Filter::class) && !is_a($value, static::class)) {
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
