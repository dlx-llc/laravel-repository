<?php

namespace LaravelRepository\Rules;

use LaravelRepository\SearchCriteria;
use Illuminate\Contracts\Validation\Rule;

class RepositorySorting implements Rule
{
    /**
     * @var array<string>
     */
    protected array $errors = [];

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (!is_string($value)) {
            $this->errors[] = __('lrepo::validation.string', compact('attribute'));
        } elseif ($params = SearchCriteria::parseSortingStr($value)) {
            if (strlen($params[0]) > 255) {
                $this->errors[] = __('lrepo::validation.max.string', [
                    'attribute' => "{$attribute}.attr",
                    'max' => 255,
                ]);
            }
        } else {
            $this->errors[] = __('lrepo::validation.repository_sort', compact('attribute'));
        }

        return empty($this->errors);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if (count($this->errors) === 1) {
            return $this->errors[0];
        } else {
            return $this->errors;
        }
    }
}
