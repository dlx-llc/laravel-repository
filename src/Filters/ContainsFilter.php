<?php

namespace LaravelRepository\Filters;

use LaravelRepository\Filter;

class ContainsFilter extends Filter
{
    use Traits\SanitizesArrayOfScalarValues;
    use Traits\ValidatesArrayOfScalarValues;

    /** @inheritdoc */
    protected function sanitizeValue(mixed $value): mixed
    {
        if (is_array($value)) {
            return $this->sanitizeArrayOfScalarValues($value);
        } else {
            return $this->sanitizeScalarValue($value);
        }
    }

    /** @inheritdoc */
    public static function validateValue(string $attribute, mixed $value): array
    {
        if (is_array($value)) {
            return static::validateArrayOfScalarValues($attribute, $value);
        } elseif (is_scalar($value)) {
            return static::validateScalarValue($attribute, $value);
        } else {
            return [__('lrepo::validation.array_or_scalar', compact('attribute'))];
        }
    }
}
