<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Rules\Validators;

class Validator
{
    use Traits\ValidatesFilters;
    use Traits\ValidatesNullValues;
    use Traits\ValidatesScalarValues;

    /**
     * Validation errors.
     *
     * @var array<string>
     */
    protected array $errors = [];

    /**
     * Checks if there are validation errors.
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
     * @param string|array<string,bool|int|float|string> $params
     */
    protected function addError(string $transKey, string|array $params): void
    {
        if (is_string($params)) {
            $params = ['attribute' => $params];
        }

        $transKey = 'larepo::validation.' . $transKey;

        /** @var string $error */
        $error = __($transKey, $params);
        $this->errors[] = $error;
    }

    protected function addErrors(string ...$messages): void
    {
        $this->errors = [...$this->errors, ...$messages];
    }
}
