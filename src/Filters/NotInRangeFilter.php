<?php

namespace LaravelRepository\Filters;

use LaravelRepository\Filter;

class NotInRangeFilter extends Filter
{
    use Traits\SanitizesScalarValue;
    use Traits\ValidatesArrayOfScalarValues;

    /** @inheritdoc */
    protected function sanitizeValue(mixed $value): mixed
    {
        if (is_array($value)) {
            $value = array_values($value);

            return [
                $this->sanitizeScalarValue($value[0]),
                $this->sanitizeScalarValue($value[1]),
            ];
        } else {
            return [0, 0];
        }
    }

    /** @inheritdoc */
    public static function validateValue(string $attribute, mixed $value): array
    {
        return static::validateArrayOfScalarValues($attribute, $value, 2);
    }
}
