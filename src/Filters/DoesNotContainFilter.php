<?php

namespace Deluxetech\LaRepo\Filters;

use Deluxetech\LaRepo\Filter;
use Deluxetech\LaRepo\Rules\Validators\Validator;

/**
 * Example:
 * {
 *   "boolean": "and",
 *   "attr": "json_arr",
 *   "operator": "!contains",
 *   "value": "example"
 * }
 */
class DoesNotContainFilter extends Filter
{
    use Traits\SanitizesArrayOfScalarValues;

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
        $validator = new Validator();
        $validator->validateScalarOrArrayOfScalar($attribute, $value);

        return $validator->getErrors();
    }
}
