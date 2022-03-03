<?php

namespace LaravelRepository\Rules;

use Illuminate\Support\Facades\App;
use Illuminate\Contracts\Validation\Rule;
use LaravelRepository\Rules\Validators\Validator;
use LaravelRepository\Contracts\FiltersCollectionParserContract;

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
            $this->errors[] = __('lrepo::validation.string', compact('attribute'));
        } elseif (empty($value)) {
            $this->errors[] = __('lrepo::validation.required', compact('attribute'));
        } elseif ($value = App::make(FiltersCollectionParserContract::class)->parse($value)) {
            $validator = new Validator();
            $validator->validateFiltersCollection($attribute, $value);
            $this->errors[] = [...$this->errors, $validator->getErrors()];
        } else {
            $this->errors[] = __('lrepo::validation.json', compact('attribute'));
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
