<?php

namespace Deluxetech\LaRepo\Filters;

use Deluxetech\LaRepo\Filter;
use Deluxetech\LaRepo\Rules\Validators\Validator;

/**
 * Example:
 * {
 *   "boolean": "and",
 *   "attr": "age",
 *   "operator": ">=",
 *   "value": "18"
 * }
 */
class IsGreaterOrEqualFilter extends Filter
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
        $validator->validateNotEmptyScalar($attribute, $value);

        return $validator->getErrors();
    }
}
