<?php

namespace Deluxetech\LaRepo\Filters;

use Deluxetech\LaRepo\Filter;
use Deluxetech\LaRepo\Rules\Validators\Validator;

/**
 * Example:
 * {
 *   "boolean": "and",
 *   "attr": "contributors",
 *   "operator": "!exists",
 *   "value": [
 *     {
 *       "attr": "age",
 *       "operator": ">",
 *       "value": "29"
 *     },
 *     {
 *       "attr": "role.name",
 *       "operator": "like",
 *       "value": "developer"
 *     }
 *   ]
 * }
 */
class RelationDoesNotExistFilter extends Filter
{
    use Traits\SanitizesFiltersCollection;

    /** @inheritdoc */
    protected function sanitizeValue(mixed $value): mixed
    {
        return $this->sanitizeFiltersCollection($value);
    }

    /** @inheritdoc */
    public static function validateValue(string $attribute, mixed $value): array
    {
        $validator = new Validator();
        $validator->validateFiltersCollection($attribute, $value);

        return $validator->getErrors();
    }
}
