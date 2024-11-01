<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 * @extends GenericRepository<Model>
 */
abstract class Repository extends GenericRepository
{
    public function __construct()
    {
        $model = $this->getModel();
        parent::__construct($model);
    }

    /**
     * Returns the fully qualified eloquent model class name.
     *
     * @return class-string<Model>
     */
    abstract protected function getModel(): string;
}
