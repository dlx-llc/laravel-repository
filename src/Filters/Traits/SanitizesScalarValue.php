<?php

namespace Deluxetech\LaRepo\Filters\Traits;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

trait SanitizesScalarValue
{
    /**
     * Sanitize a value that should be an array.
     *
     * @param  mixed $value
     * @return bool|int|float|string
     */
    protected function sanitizeScalarValue(mixed $value): bool|int|float|string
    {
        if (is_string($value)) {
            return $this->parseDateString($value);
        } elseif (is_scalar($value)) {
            return $value;
        } elseif (is_object($value) && method_exists($value, '__toString')) {
            return $value->__toString();
        } else {
            return '';
        }
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
