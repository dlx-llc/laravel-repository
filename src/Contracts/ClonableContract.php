<?php

namespace Deluxetech\LaRepo\Contracts;

interface ClonableContract
{
    /**
     * Clones the object and returns the identical object instance.
     *
     * @return static
     */
    public function clone(): static;
}
