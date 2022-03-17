<?php

namespace Deluxetech\LaRepo\Eloquent;

use Deluxetech\LaRepo\Contracts\DataManipulatorContract;

abstract class Repository extends ReadonlyRepository implements DataManipulatorContract
{
    /** @inheritdoc */
    public function create(array $attributes): object
    {
        return $this->query->getModel()->create($attributes);
    }

    /** @inheritdoc */
    public function update(object $model, array $attributes): void
    {
        $model->update($attributes);
    }

    /** @inheritdoc */
    public function delete(object $model): void
    {
        $model->delete();
    }
}
