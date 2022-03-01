<?php

namespace LaravelRepository;

use LaravelRepository\Contracts\RepositoryContract;

/**
 * @todo
 * 1) Add cache driver
 * 3) Add ability to cache eloquent records and prefer cache or source
 * 4) Use interfaces for search criteria and its components
 */
class GenericRepository extends ReadonlyGenericRepository implements RepositoryContract
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
