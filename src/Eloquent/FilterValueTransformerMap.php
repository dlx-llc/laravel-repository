<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Eloquent;

use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;

class FilterValueTransformerMap
{
    /**
     * A model to transformer map.
     *
     * @var array<class-string<Model>,FilterValueTransformer>
     */
    protected array $items = [];

    public function has(Model $model): bool
    {
        return isset($this->items[$model::class]);
    }

    public function get(Model $model): ?FilterValueTransformer
    {
        return $this->items[$model::class] ?? null;
    }

    public function create(Model $model): FilterValueTransformer
    {
        $this->items[$model::class] = App::makeWith(
            FilterValueTransformer::class,
            ['model' => $model],
        );

        return $this->items[$model::class];
    }
}
