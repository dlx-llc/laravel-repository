<?php

namespace Deluxetech\LaRepo\Rules;

use Illuminate\Support\Facades\App;
use Illuminate\Contracts\Validation\Rule;
use Deluxetech\LaRepo\Contracts\TextSearchFormatterContract;

class RepositoryTextSearch implements Rule
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
            $this->errors[] = __('larepo::validation.string', ['attribute' => $attribute]);
        } elseif ($params = App::make(TextSearchFormatterContract::class)->parse($value)) {
            $this->validateText($params[0], $attribute);
            $this->validateAttrs($params[1], $attribute);
        } else {
            $this->errors[] = __('larepo::validation.repository_search', compact('attribute'));
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

    /**
     * Validate the search text parameter.
     *
     * @param  string $text
     * @param  string $attribute
     * @return void
     */
    protected function validateText(string $text, string $attribute): void
    {
        if (strlen($text) > 255) {
            $this->errors[] = __('larepo::validation.max.string', [
                'attribute' => "{$attribute}.text",
                'max' => 255,
            ]);
        }
    }

    /**
     * Validate the data attribute names.
     *
     * @param  array $attrs
     * @param  string $attribute
     * @return void
     */
    protected function validateAttrs(array $attrs, string $attribute): void
    {
        foreach ($attrs as $i => $attr) {
            if (strlen($attr) > 255) {
                $this->errors[] = __('larepo::validation.max.string', [
                    'attribute' => "{$attribute}.attrs.{$i}",
                    'max' => 255,
                ]);
            }
        }
    }
}
