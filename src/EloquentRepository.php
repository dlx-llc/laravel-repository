<?php

namespace Deluxetech\LaRepo;

use Deluxetech\LaRepo\Strategies\EloquentStrategy;
use Deluxetech\LaRepo\Contracts\RepositoryStrategyContract;

class EloquentRepository extends Repository
{
    /**
     * Class constructor.
     *
     * @param  string $model
     * @return void
     */
    public function __construct(protected string $model)
    {
        parent::__construct();
    }

    /** @inheritdoc */
    protected function createStrategy(): RepositoryStrategyContract
    {
        $strategy = new EloquentStrategy($this->model);

        return $strategy;
    }
}
