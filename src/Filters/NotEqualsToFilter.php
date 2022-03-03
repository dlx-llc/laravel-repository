<?php

namespace LaravelRepository\Filters;

use LaravelRepository\Filter;
use LaravelRepository\Rules\Validators\Validator;

/**
 * Example:
 * {
 *   "orCond": false,
 *   "attr": "first_name",
 *   "mode": "!=",
 *   "value": "John"
 * }
 */
class NotEqualsToFilter extends Filter
{
    use Traits\SanitizesScalarValue;

    /** @inheritdoc */
    protected function sanitizeValue(mixed $value): mixed
    {
        return $this->sanitizeScalarValue($value);
    }

    /** @inheritdoc */
    public static function validateValue(string $attribute, mixed $value): array
    {
        $validator = new Validator();
        $validator->validateScalar($attribute, $value);

        return $validator->getErrors();
    }
}
