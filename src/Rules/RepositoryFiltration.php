<?php

namespace Deluxetech\LaRepo\Rules;

use Illuminate\Support\Facades\App;
use Illuminate\Contracts\Validation\Rule;
use Deluxetech\LaRepo\Rules\Validators\Validator;
use Deluxetech\LaRepo\Contracts\FiltersCollectionFormatterContract;

class RepositoryFiltration implements Rule
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
        } elseif (empty($value)) {
            $this->errors[] = __('larepo::validation.required', compact('attribute'));
        } elseif ($value = App::make(FiltersCollectionFormatterContract::class)->parse($value)) {
            $validator = new Validator();
            $validator->validateFiltersArr($attribute, $value);
            $this->errors = [...$this->errors, ...$validator->getErrors()];
        } else {
            $this->errors[] = __('larepo::validation.json', compact('attribute'));
        }

        return empty($this->errors);
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
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
