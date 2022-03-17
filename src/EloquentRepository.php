<?php

namespace Deluxetech\LaRepo;

use Deluxetech\LaRepo\Contracts\RepositoryContract;

abstract class EloquentRepository extends ImmutableEloquentRepository implements RepositoryContract
{
    /** @inheritdoc */
    public function create(array $attributes): object
    {
        return $this->strategy->create($attributes);
    }

    /** @inheritdoc */
    public function update(object $model, array $attributes): void
    {
        $this->strategy->update($model, $attributes);
    }

    /** @inheritdoc */
    public function delete(object $model): void
    {
        $this->strategy->delete($model);
    }
}
