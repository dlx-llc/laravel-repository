<?php

namespace Deluxetech\LaRepo;

use Deluxetech\LaRepo\Strategies\EloquentStrategy;
use Deluxetech\LaRepo\Contracts\RepositoryStrategyContract;

class EloquentRepository extends Repository
{
    /** @inheritdoc */
    protected function createStrategy(): RepositoryStrategyContract
    {
        $dataSource = $this->domainMapper->getDataSource();

        return new EloquentStrategy($dataSource);
    }
}
