<?php

namespace LaravelRepository\Filters;

use LaravelRepository\Filter;
use LaravelRepository\Rules\Validators\Validator;

/**
 * Example:
 * {
 *   "orCond": false,
 *   "attr": "title",
 *   "mode": "!like",
 *   "value": "deluxe"
 * }
 */
class IsNotLikeFilter extends Filter
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
