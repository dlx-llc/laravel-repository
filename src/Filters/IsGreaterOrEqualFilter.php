<?php

declare(strict_types=1);

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
 *
 * @extends Filter<bool|int|float|string>
 */
class IsGreaterOrEqualFilter extends Filter
{
    use Traits\SanitizesScalarValue;

    public static function validateValue(string $attribute, mixed $value): array
    {
        $validator = new Validator();
        $validator->validateNotEmptyScalar($attribute, $value);

        return $validator->getErrors();
    }

    protected function sanitizeValue(mixed $value): mixed
    {
        return $this->sanitizeScalarValue($value);
    }
}
