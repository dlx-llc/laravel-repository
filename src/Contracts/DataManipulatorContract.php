<?php

namespace Deluxetech\LaRepo\Contracts;

interface DataManipulatorContract
{
    /**
     * Creates a new data model and returns the instance.
     *
     * @param  array $attributes
     * @return object
     */
    public function create(array $attributes): object;

    /**
     * Updates the given data model.
     *
     * @param  object $model
     * @param  array $attributes
     * @return void
     */
    public function update(object $model, array $attributes): void;

    /**
     * Deletes the given data model.
     *
     * @param  object $model
     * @return void
     */
    public function delete(object $model): void;
}
