<?php

namespace Deluxetech\LaRepo;

use Deluxetech\LaRepo\Contracts\RepositoryContract;

abstract class Repository extends ImmutableRepository implements RepositoryContract
{
    /** @inheritdoc */
    public function create(array $attributes): object
    {
        return $this->db->create($attributes);
    }

    /** @inheritdoc */
    public function update(object $model, array $attributes): void
    {
        $this->db->update($model, $attributes);
    }

    /** @inheritdoc */
    public function delete(object $model): void
    {
        $this->db->delete($model);
    }
}
