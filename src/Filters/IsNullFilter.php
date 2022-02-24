<?php

namespace LaravelRepository\Filters;

use LaravelRepository\Filter;

class IsNullFilter extends Filter
{
    use Traits\ValidatesNullValue;

    /** @inheritdoc */
    protected function sanitizeValue(mixed $value): mixed
    {
        return null;
    }

    /** @inheritdoc */
    public static function validateValue(string $attribute, mixed $value): array
    {
        return static::validateNullValue($attribute, $value);
    }
}
