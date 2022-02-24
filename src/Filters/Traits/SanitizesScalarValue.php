<?php

namespace LaravelRepository\Filters\Traits;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

trait SanitizesScalarValue
{
    /**
     * Sanitize a value that should be an array.
     *
     * @param  mixed $value
     * @return mixed
     */
    protected function sanitizeScalarValue(mixed $value): mixed
    {
        if (is_string($value)) {
            $value = $this->parseDateString($value);
        } elseif (!is_scalar($value)) {
            $value = '';
        }

        return $value;
    }

    /**
     * Checks if a date string is given and converts it to the standard date format.
     * Otherwise, returns the same given value.
     *
     * @param  string $value
     * @return string
     */
    protected function parseDateString(string $value): string
    {
        // Prevent phone and timestamp-like numbers to be converted into dates
        if (preg_match('/^\+?\d+$/', $value)) {
            return $value;
        }

        $validator = Validator::make(['value' => $value], ['value' => 'required|date']);

        if (!$validator->fails()) {
            return Carbon::parse($value)->format('Y-m-d H:i:s');
        }

        return $value;
    }
}
