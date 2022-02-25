<?php

namespace LaravelRepository\Contracts;

interface DbDriverContract extends DataReaderContract, DataManipulatorContract
{
    /**
     * Initializes the DB driver.
     *
     * @param  mixed $dbContext
     * @return static
     */
    public static function init(mixed $dbContext): static;

    /**
     * Specifies that duplicate records should be excluded.
     *
     * @return static
     */
    public function distinct(): static;
}
