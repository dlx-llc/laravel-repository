<?php

namespace Deluxetech\LaRepo\Filters;

use Deluxetech\LaRepo\Filter;
use Deluxetech\LaRepo\Rules\Validators\Validator;

/**
 * Example:
 * {
 *   "boolean": "and",
 *   "attr": "deleted_at",
 *   "operator": "null"
 * }
 */
class IsNullFilter extends Filter
{
    public static function validateValue(string $attribute, mixed $value): array
    {
        $validator = new Validator();
        $validator->validateNull($attribute, $value);

        return $validator->getErrors();
    }

    public function hasValue(): bool
    {
        return false;
    }

    protected function sanitizeValue(mixed $value): mixed
    {
        return null;
    }
}
