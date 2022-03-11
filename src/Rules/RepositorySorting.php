<?php

namespace Deluxetech\LaRepo\Rules;

use Illuminate\Support\Facades\App;
use Illuminate\Contracts\Validation\Rule;
use Deluxetech\LaRepo\Contracts\SortingFormatterContract;

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
            $this->errors[] = __('larepo::validation.string', compact('attribute'));
        } elseif ($params = App::make(SortingFormatterContract::class)->parse($value)) {
            if (strlen($params[0]) > 255) {
                $this->errors[] = __('larepo::validation.max.string', [
                    'attribute' => "{$attribute}.attr",
                    'max' => 255,
                ]);
            }
        } else {
            $this->errors[] = __('larepo::validation.repository_sort', compact('attribute'));
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
