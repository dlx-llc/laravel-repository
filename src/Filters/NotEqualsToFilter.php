<?php

namespace Deluxetech\LaRepo\Filters;

use Deluxetech\LaRepo\Filter;
use Deluxetech\LaRepo\Rules\Validators\Validator;

/**
 * Example:
 * {
 *   "boolean": "and",
 *   "attr": "first_name",
 *   "operator": "!=",
 *   "value": "John"
 * }
 */
class NotEqualsToFilter extends Filter
{
    use Traits\SanitizesScalarValue;

    protected function sanitizeValue(mixed $value): mixed
    {
        return $this->sanitizeScalarValue($value);
    }

    public static function validateValue(string $attribute, mixed $value): array
    {
        $validator = new Validator();
        $validator->validateScalar($attribute, $value);

        return $validator->getErrors();
    }
}
