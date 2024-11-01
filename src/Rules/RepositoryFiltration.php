<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Rules;

use Closure;
use Illuminate\Support\Facades\App;
use Deluxetech\LaRepo\Rules\Validators\Validator;
use Illuminate\Contracts\Validation\ValidationRule;
use Deluxetech\LaRepo\Contracts\FiltersCollectionFormatterContract;

class RepositoryFiltration implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $errors = [];

        if (!is_string($value)) {
            $errors[] = __('larepo::validation.string', compact('attribute'));
        } elseif (empty($value)) {
            $errors[] = __('larepo::validation.required', compact('attribute'));
        } elseif ($value = App::make(FiltersCollectionFormatterContract::class)->parse($value)) {
            $validator = new Validator();
            $validator->validateFiltersArr($attribute, $value);
            $errors = [...$errors, ...$validator->getErrors()];
        } else {
            $errors[] = __('larepo::validation.json', compact('attribute'));
        }

        if ($errors) {
            // @phpstan-ignore-next-line
            $fail(...$errors);
        }
    }
}
