<?php

namespace LaravelRepository\Filters\Traits;

trait ValidatesArrayOfScalarValues
{
    use ValidatesScalarValue;

    /**
     * Checks if the given value is an array of not empty scalar values.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @param  int|null $size
     * @return array
     */
    protected static function validateArrayOfNotEmptyScalarValues(
        string $attribute,
        mixed $value,
        ?int $size = null
    ): array {
        $errors = [];

        if (!is_array($value)) {
            $errors[] = __('lrepo::validation.array', compact('attribute'));
        } elseif (empty($value)) {
            $errors[] = __('lrepo::validation.required', compact('attribute'));
        } else {
            if (isset($size) && count($value) !== $size) {
                $errors[] = __('lrepo::validation.size.array', compact('attribute', 'size'));
            }

            foreach ($value as $i => $item) {
                if ($itemErr = static::validateNotEmptyScalarValue("{$attribute}.{$i}", $item)) {
                    $errors = [...$errors, ...$itemErr];
                }
            }
        }

        return [];
    }

    /**
     * Checks if the given value is an array of scalar values.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @param  int|null $size
     * @return array
     */
    protected static function validateArrayOfScalarValues(
        string $attribute,
        mixed $value,
        ?int $size = null
    ): array {
        $errors = [];

        if (!is_array($value)) {
            $errors[] = __('lrepo::validation.array', compact('attribute'));
        } elseif (empty($value)) {
            $errors[] = __('lrepo::validation.required', compact('attribute'));
        } else {
            if (isset($size) && count($value) !== $size) {
                $errors[] = __('lrepo::validation.size.array', compact('attribute', 'size'));
            }

            foreach ($value as $i => $item) {
                if ($itemErr = static::validateScalarValue("{$attribute}.{$i}", $item)) {
                    $errors = [...$errors, ...$itemErr];
                }
            }
        }

        return [];
    }
}
