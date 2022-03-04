<?php

namespace LaravelRepository\Filters;

use LaravelRepository\Filter;
use LaravelRepository\Rules\Validators\Validator;

/**
 * Example:
 * {
 *   "operator": "and",
 *   "attr": "contributors",
 *   "mode": "!exists",
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
