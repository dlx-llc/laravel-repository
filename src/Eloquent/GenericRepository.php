<?php

namespace Deluxetech\LaRepo\Eloquent;

class GenericRepository extends Repository
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
    public function getModel(): string
    {
        return $this->model;
    }
}
