<?php

namespace Deluxetech\LaRepo\Filters;

use Deluxetech\LaRepo\Filter;
use Deluxetech\LaRepo\Rules\Validators\Validator;

/**
 * Example:
 * {
 *   "operator": "and",
 *   "attr": "contributors",
 *   "mode": "exists",
 *   "value": [
 *     {
 *       "attr": "age",
 *       "mode": ">",
 *       "value": "29"
 *     },
 *     {
 *       "attr": "role.name",
 *       "mode": "like",
 *       "value": "developer"
 *     }
 *   ]
 * }
 */
class RelationExistsFilter extends Filter
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
