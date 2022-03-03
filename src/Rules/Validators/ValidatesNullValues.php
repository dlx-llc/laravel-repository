<?php

namespace LaravelRepository\Rules\Validators;

trait ValidatesNullValues
{
    /**
     * Checks if the given value is equal to null.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
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
