<?php

namespace Deluxetech\LaRepo\Eloquent;

use Deluxetech\LaRepo\Repository;
use Deluxetech\LaRepo\Contracts\RepositoryStrategyContract;

class EloquentRepository extends Repository
{
    /**
     * The eloquent model class name.
     *
     * @var string
     */
    protected string $model;

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

    /** @inheritdoc */
    public function createStrategy(): RepositoryStrategyContract
    {
        return new EloquentStrategy($this->model);
    }
}
