<?php

namespace Deluxetech\LaRepo;

class GenericEloquentRepository extends EloquentRepository
{
    /**
     * Class constructor.
     *
     * @param  string $model
     * @return void
     */
    public function __construct(string $model)
    {
        $this->model = $model;
        parent::__construct();
    }
}
