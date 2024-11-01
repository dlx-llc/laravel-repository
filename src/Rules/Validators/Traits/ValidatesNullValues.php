<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Rules\Validators\Traits;

trait ValidatesNullValues
{
    /**
     * Checks if the given value is equal to null.
     */
    public function validateNull(string $attribute, mixed $value): bool
    {
        if (!is_null($value)) {
            $this->addError('absent', $attribute);

            return false;
        }

        return true;
    }
}
