<?php

namespace LaravelRepository\Filters;

use LaravelRepository\Filter;
use LaravelRepository\Rules\Validators\Validator;

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
