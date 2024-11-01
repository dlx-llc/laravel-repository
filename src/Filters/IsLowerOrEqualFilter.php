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
 *   "operator": "<=",
 *   "value": "17"
 * }
 *
 * @extends Filter<bool|int|float|string>
 */
class IsLowerOrEqualFilter extends Filter
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
