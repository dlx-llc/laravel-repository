<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Filters;

use Deluxetech\LaRepo\Filter;
use Deluxetech\LaRepo\Rules\Validators\Validator;

/**
 * Example:
 * {
 *   "boolean": "and",
 *   "attr": "json_arr",
 *   "operator": "contains",
 *   "value": "example"
 * }
 *
 * @extends Filter<bool|int|float|string|array<bool|int|float|string>>
 */
class ContainsFilter extends Filter
{
    use Traits\SanitizesArrayOfScalarValues;

    public static function validateValue(string $attribute, mixed $value): array
    {
        $validator = new Validator();
        $validator->validateScalarOrArrayOfScalar($attribute, $value);

        return $validator->getErrors();
    }

    protected function sanitizeValue(mixed $value): mixed
    {
        if (is_array($value)) {
            return $this->sanitizeArrayOfScalarValues($value);
        }

        return $this->sanitizeScalarValue($value);
    }
}
