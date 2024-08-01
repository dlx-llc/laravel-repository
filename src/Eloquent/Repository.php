<?php

namespace Deluxetech\LaRepo\Eloquent;

use Illuminate\Database\Eloquent\Model;

abstract class Repository extends GenericRepository
{
    /**
     * Returns the fully qualified eloquent model class name.
     *
     * @return class-string<Model>
     */
    abstract protected function getModel(): string;

    public function __construct()
    {
        $model = $this->getModel();
        parent::__construct($model);
    }
}
