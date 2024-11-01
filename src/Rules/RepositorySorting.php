<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Rules;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Contracts\Validation\ValidationRule;
use Deluxetech\LaRepo\Contracts\SortingFormatterContract;

class RepositorySorting implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $errors = [];

        if (!is_string($value)) {
            $errors[] = __('larepo::validation.string', compact('attribute'));
        } elseif ($params = App::make(SortingFormatterContract::class)->parse($value)) {
            if (strlen($params[0]) > 255) {
                $errors[] = __('larepo::validation.max.string', [
                    'attribute' => "{$attribute}.attr",
                    'max' => 255,
                ]);
            }
        } else {
            $errors[] = __('larepo::validation.repository_sort', compact('attribute'));
        }

        if ($errors) {
            // @phpstan-ignore-next-line
            $fail(...$errors);
        }
    }
}
