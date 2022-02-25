<?php

namespace LaravelRepository\Filters;

use LaravelRepository\Filter;

/**
 * Example:
 * {
 *   "orCond": false,
 *   "attr": "first_name",
 *   "mode": "!included",
 *   "value": ["John", "Jack", "Jenny"]
 * }
 */
class NotIncludedInFilter extends Filter
{
    use Traits\SanitizesArrayOfScalarValues;
    use Traits\ValidatesArrayOfScalarValues;

    /** @inheritdoc */
    protected function sanitizeValue(mixed $value): mixed
    {
        return $this->sanitizeArrayOfScalarValues($value);
    }

    /** @inheritdoc */
    public static function validateValue(string $attribute, mixed $value): array
    {
        return static::validateArrayOfScalarValues($attribute, $value);
    }
}
