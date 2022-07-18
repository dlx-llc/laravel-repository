<?php

namespace Deluxetech\LaRepo\Eloquent;

abstract class EloquentRepository extends GenericEloquentRepository
{
    /**
     * Returns the fully qualified eloquent model class name.
     *
     * @return string
     */
    abstract protected function getModel(): string;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $model = $this->getModel();
        parent::__construct($model);
    }
}
