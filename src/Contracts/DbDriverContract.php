<?php

namespace LaravelRepository\Contracts;

interface DbDriverContract extends DataReaderContract, DataManipulatorContract
{
    /**
     * Initializes the DB driver.
     *
     * @param  object $dbContext
     * @return static
     */
    public static function init(object $dbContext): static;

    /**
     * Specifies that duplicate records should be excluded.
     *
     * @return static
     */
    public function distinct(): static;
}
