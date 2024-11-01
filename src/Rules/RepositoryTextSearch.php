<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Rules;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Contracts\Validation\ValidationRule;
use Deluxetech\LaRepo\Contracts\TextSearchFormatterContract;

class RepositoryTextSearch implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $errors = [];

        if (!is_string($value)) {
            $errors[] = __('larepo::validation.string', compact('attribute'));
        } elseif ($params = App::make(TextSearchFormatterContract::class)->parse($value)) {
            if ($error = $this->validateSearchText($attribute, $params[0])) {
                $errors[] = $error;
            }

            if ($error = $this->validateSearchAttrs($attribute, ...$params[1])) {
                $errors = [...$errors, ...$error];
            }
        } else {
            $errors[] = __('larepo::validation.repository_search', compact('attribute'));
        }

        if ($errors) {
            // @phpstan-ignore-next-line
            $fail(...$errors);
        }
    }

    protected function validateSearchText(string $attribute, string $text): ?string
    {
        if (strlen($text) > 255) {
            /** @var string $message */
            $message = __('larepo::validation.max.string', [
                'attribute' => "{$attribute}.text",
                'max' => 255,
            ]);

            return $message;
        }

        return null;
    }

    /**
     * @return array<string>
     */
    protected function validateSearchAttrs(string $attribute, string ...$searchAttributes): array
    {
        $errors = [];

        foreach ($searchAttributes as $i => $attr) {
            if (strlen($attr) > 255) {
                /** @var string $message */
                $message = __('larepo::validation.max.string', [
                    'attribute' => "{$attribute}.attrs.{$i}",
                    'max' => 255,
                ]);

                $errors[] = $message;
            }
        }

        return $errors;
    }
}
