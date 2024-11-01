<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Filters;

use Deluxetech\LaRepo\Filter;
use Deluxetech\LaRepo\Rules\Validators\Validator;

/**
 * Example:
 * {
 *   "boolean": "and",
 *   "attr": "date_of_birth",
 *   "operator": "range",
 *   "value": ["1990-01-01", "1999-12-31"]
 * }
 *
 * @extends Filter<array{0:bool|int|float|string,1:bool|int|float|string}>
 */
class InRangeFilter extends Filter
{
    use Traits\SanitizesScalarValue;

    public static function validateValue(string $attribute, mixed $value): array
    {
        $validator = new Validator();
        $validator->validateArrayOfScalar($attribute, $value);

        return $validator->getErrors();
    }

    protected function sanitizeValue(mixed $value): mixed
    {
        if (is_array($value)) {
            $value = array_values($value);

            return [
                $this->sanitizeScalarValue($value[0]),
                $this->sanitizeScalarValue($value[1]),
            ];
        }

        return [0, 0];
    }
}
