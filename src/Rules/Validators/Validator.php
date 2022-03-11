<?php

namespace Deluxetech\LaRepo\Rules\Validators;

class Validator
{
    use ValidatesFilters;
    use ValidatesNullValues;
    use ValidatesScalarValues;

    /**
     * Validation errors.
     *
     * @var array<string>
     */
    protected array $errors = [];

    /**
     * Checks if there are validation errors.
     *
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Returns the validation errors.
     *
     * @return array<string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Adds an error message.
     *
     * @param  string $transKey
     * @param  string|array $params
     * @return void
     */
    protected function addError(string $transKey, string|array $params): void
    {
        if (is_string($params)) {
            $params = ['attribute' => $params];
        }

        $transKey = 'larepo::validation.' . $transKey;
        $this->errors[] = __($transKey, $params);
    }

    /**
     * Adds error messages.
     *
     * @param  string ...$messages
     * @return void
     */
    protected function addErrors(string ...$messages): void
    {
        $this->errors = [...$this->errors, ...$messages];
    }
}
