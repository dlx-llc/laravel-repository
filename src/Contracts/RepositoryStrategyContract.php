<?php

namespace Deluxetech\LaRepo\Contracts;

interface RepositoryStrategyContract extends DataReaderContract, DataManipulatorContract
{
    /**
     * Class constructor.
     *
     * @param  mixed $source  The data source. Can be a table name, a model
     *                        class name or anything else.
     * @return static
     */
    public function __construct(mixed $source);

    /**
     * Returns the current query object.
     *
     * @return object
     */
    public function getQuery(): object;

    /**
     * Specifies that duplicate records should be excluded.
     *
     * @return static
     */
    public function distinct(): static;

    /**
     * Filters query by the given key value pair.
     *
     * @param  string $attr
     * @param  mixed $operator
     * @param  mixed $value
     * @return static
     */
    public function where(string $attr, mixed $operator, mixed $value = null): static;

    /**
     * Filters query by the given key value pair.
     *
     * @param  string $attr
     * @param  mixed $operator
     * @param  mixed $value
     * @return static
     */
    public function orWhere(string $attr, mixed $operator, mixed $value = null): static;

    /**
     * Filters query where the value for the given attribute is null.
     *
     * @param  string $attr
     * @return static
     */
    public function whereNull(string $attr): static;

    /**
     * Filters query where the value for the given attribute is null.
     *
     * @param  string $attr
     * @return static
     */
    public function orWhereNull(string $attr): static;

    /**
     * Filters query where the value for the given attribute is not null.
     *
     * @param  string $attr
     * @return static
     */
    public function whereNotNull(string $attr): static;

    /**
     * Filters query where the value for the given attribute is not null.
     *
     * @param  string $attr
     * @return static
     */
    public function orWhereNotNull(string $attr): static;

    /**
     * Filters query by the given key value pair.
     *
     * @param  string $attr
     * @param  array $values
     * @return static
     */
    public function whereIn(string $attr, array $values): static;

    /**
     * Filters query by the given key value pair.
     *
     * @param  string $attr
     * @param  array $values
     * @return static
     */
    public function orWhereIn(string $attr, array $values): static;

    /**
     * Filters query by the given key value pair.
     *
     * @param  string $attr
     * @param  array $values
     * @return static
     */
    public function whereNotIn(string $attr, array $values): static;

    /**
     * Filters query by the given key value pair.
     *
     * @param  string $attr
     * @param  array $values
     * @return static
     */
    public function orWhereNotIn(string $attr, array $values): static;
}
