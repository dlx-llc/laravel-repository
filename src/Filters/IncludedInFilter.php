<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Filters;

use Deluxetech\LaRepo\Filter;
use Deluxetech\LaRepo\Rules\Validators\Validator;

/**
 * Example:
 * {
 *   "boolean": "and",
 *   "attr": "first_name",
 *   "operator": "included",
 *   "value": ["John", "Jack", "Jenny"]
 * }
 *
 * @extends Filter<array<bool|int|float|string>>
 */
class IncludedInFilter extends Filter
{
    use Traits\SanitizesArrayOfScalarValues;

    public static function validateValue(string $attribute, mixed $value): array
    {
        $validator = new Validator();
        $validator->validateArrayOfScalar($attribute, $value);

        return $validator->getErrors();
    }

    protected function sanitizeValue(mixed $value): mixed
    {
        return $this->sanitizeArrayOfScalarValues($value);
    }
}
