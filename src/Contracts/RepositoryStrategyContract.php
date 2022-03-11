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
     * Specifies that duplicate records should be excluded.
     *
     * @return static
     */
    public function distinct(): static;
}