<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Eloquent\Filtration;

use Illuminate\Database\Eloquent\Model;
use Deluxetech\LaRepo\FilterValueTransformer as BaseFilterValueTransformer;

class FilterValueTransformer extends BaseFilterValueTransformer
{
    public function __construct(private readonly Model $model)
    {
    }

    /**
     * Adds transformers for creation and update datetime columns.
     */
    public function addTimestamps(): self
    {
        if ($this->model->usesTimestamps()) {
            if ($createdAtColumn = $this->model->getCreatedAtColumn()) {
                $this->shouldBeDatetime($createdAtColumn);
            }

            if ($updatedAtColumn = $this->model->getUpdatedAtColumn()) {
                $this->shouldBeDatetime($updatedAtColumn);
            }
        }

        return $this;
    }

    /**
     * Adds a transformer for the soft delete datetime column.
     */
    public function addSoftDeleteTimestamp(): self
    {
        if (method_exists($this->model, 'getDeletedAtColumn')) {
            if ($deletedAtColumn = $this->model->getDeletedAtColumn()) {
                $this->shouldBeDatetime($deletedAtColumn);
            }
        }

        return $this;
    }

    /**
     * Adds transformers for casted date(time) attributes.
     */
    public function addCasts(): self
    {
        foreach ($this->model->getCasts() as $attr => $cast) {
            match ($cast) {
                'date', 'immutable_date' => $this->shouldBeDate($attr),
                'datetime', 'immutable_datetime' => $this->shouldBeDatetime($attr),
                default => null,
            };
        }

        return $this;
    }
}
