<?php

namespace Deluxetech\LaRepo;

use Deluxetech\LaRepo\Strategies\EloquentStrategy;
use Deluxetech\LaRepo\Contracts\LoadContextContract;

abstract class EloquentRepository extends Repository
{
    /**
     * The eloquent model class.
     *
     * @var string
     */
    protected string $model;

    /** @inheritdoc */
    protected function createStrategy(): EloquentStrategy
    {
        $strategy = new EloquentStrategy($this->model);

        return $strategy;
    }

    /** @inheritdoc */
    public function setLoadContext(LoadContextContract $context): static
    {
        $this->applyLoadContext($this->strategy->getQuery(), $context);

        return $this;
    }

    /**
     * Recursively loads the required relations.
     *
     * @param  object $query
     * @param  LoadContextContract $context
     * @return void
     */
    protected function applyLoadContext(object $query, LoadContextContract $context): void
    {
        if ($attrs = $context->getAttributes()) {
            $query->select($attrs);
        }

        foreach ($context->getRelations() as $key => $value) {
            if (is_int($key)) {
                $query->with($value);
            } elseif (is_string($key)) {
                if (is_subclass_of($value, LoadContextContract::class)) {
                    $query->with($key, function ($query) use ($value) {
                        $this->applyLoadContext($query, $value);
                    });
                } else {
                    $query->with($key);
                }
            }
        }

        if ($counts = $context->getRelationCounts()) {
            $query->withCount($counts);
        }
    }
}
