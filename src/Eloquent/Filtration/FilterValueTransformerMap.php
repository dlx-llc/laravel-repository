<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Eloquent\Filtration;

use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;

class FilterValueTransformerMap
{
    /**
     * A model to transformer map.
     *
     * @var array<class-string<Model>,FilterValueTransformer>
     */
    protected array $map = [];

    public function has(Model $model): bool
    {
        return isset($this->map[$model::class]);
    }

    public function get(Model $model): ?FilterValueTransformer
    {
        return $this->map[$model::class] ?? null;
    }

    public function create(Model $model): FilterValueTransformer
    {
        $this->map[$model::class] = App::makeWith(
            FilterValueTransformer::class,
            ['model' => $model],
        );

        return $this->map[$model::class];
    }
}
