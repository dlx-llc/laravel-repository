<?php

namespace Deluxetech\LaRepo\Filters;

use Deluxetech\LaRepo\Filter;
use Deluxetech\LaRepo\Rules\Validators\Validator;

/**
 * Example:
 * {
 *   "operator": "and",
 *   "attr": "first_name",
 *   "mode": "included",
 *   "value": ["John", "Jack", "Jenny"]
 * }
 */
class IncludedInFilter extends Filter
{
    use Traits\SanitizesArrayOfScalarValues;

    /** @inheritdoc */
    protected function sanitizeValue(mixed $value): mixed
    {
        return $this->sanitizeArrayOfScalarValues($value);
    }

    /** @inheritdoc */
    public static function validateValue(string $attribute, mixed $value): array
    {
        $validator = new Validator();
        $validator->validateArrayOfScalar($attribute, $value);

        return $validator->getErrors();
    }
}
