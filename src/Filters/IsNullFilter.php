<?php

namespace Deluxetech\LaRepo\Filters;

use Deluxetech\LaRepo\Filter;
use Deluxetech\LaRepo\Rules\Validators\Validator;

/**
 * Example:
 * {
 *   "operator": "and",
 *   "attr": "deleted_at",
 *   "mode": "null"
 * }
 */
class IsNullFilter extends Filter
{
    /** @inheritdoc */
    protected function sanitizeValue(mixed $value): mixed
    {
        return null;
    }

    /** @inheritdoc */
    public static function validateValue(string $attribute, mixed $value): array
    {
        $validator = new Validator();
        $validator->validateNull($attribute, $value);

        return $validator->getErrors();
    }
}
