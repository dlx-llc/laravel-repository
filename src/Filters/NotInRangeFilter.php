<?php

namespace Deluxetech\LaRepo\Filters;

use Deluxetech\LaRepo\Filter;
use Deluxetech\LaRepo\Rules\Validators\Validator;

/**
 * Example:
 * {
 *   "operator": "and",
 *   "attr": "date_of_birth",
 *   "mode": "!range",
 *   "value": ["1990-01-01", "1999-12-31"]
 * }
 */
class NotInRangeFilter extends Filter
{
    use Traits\SanitizesScalarValue;

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
        $validator = new Validator();
        $validator->validateArrayOfScalar($attribute, $value);

        return $validator->getErrors();
    }
}
