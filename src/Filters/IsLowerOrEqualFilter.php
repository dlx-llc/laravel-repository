<?php

namespace LaravelRepository\Filters;

use LaravelRepository\Filter;

class IsLowerOrEqualFilter extends Filter
{
    use Traits\SanitizesScalarValue;
    use Traits\ValidatesScalarValue;

    /** @inheritdoc */
    protected function sanitizeValue(mixed $value): mixed
    {
        return $this->sanitizeScalarValue($value);
    }

    /** @inheritdoc */
    public static function validateValue(string $attribute, mixed $value): array
    {
        return static::validateNotEmptyScalarValue($attribute, $value);
    }
}
