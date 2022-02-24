<?php

namespace LaravelRepository\Filters\Traits;

trait ValidatesScalarValue
{
    /**
     * Checks if the given value is a scalar type and isn't empty.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return array
     */
    protected static function validateNotEmptyScalarValue(string $attribute, mixed $value): array
    {
        if ($errors = static::validateScalarValue($attribute, $value)) {
            return $errors;
        } elseif ($value === '') {
            return [__('lrepo::validation.required', compact('attribute'))];
        }

        return [];
    }

    /**
     * Checks if the given value is a scalar type.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return array
     */
    protected static function validateScalarValue(string $attribute, mixed $value): array
    {
        if (!is_scalar($value)) {
            return [__('lrepo::validation.scalar', compact('attribute'))];
        }

        return [];
    }
}
