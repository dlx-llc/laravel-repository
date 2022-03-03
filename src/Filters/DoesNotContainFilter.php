<?php

namespace LaravelRepository\Filters;

use LaravelRepository\Filter;
use LaravelRepository\Rules\Validators\Validator;

/**
 * Example:
 * {
 *   "orCond": false,
 *   "attr": "json_arr",
 *   "mode": "!contains",
 *   "value": "anything"
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
