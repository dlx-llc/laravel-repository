<?php

namespace LaravelRepository\Contracts;

interface DbDriverContract extends DataReaderContract, DataManipulatorContract
{
    /**
     * Creates a new DB driver object.
     *
     * @param  object $dbContext
     * @return static
     */
    public static function make(object $dbContext): static;

    /**
     * Specifies that duplicate records should be excluded.
     *
     * @return static
     */
    public function distinct(): static;
}
