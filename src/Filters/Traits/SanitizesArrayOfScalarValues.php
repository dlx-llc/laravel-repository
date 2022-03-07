<?php

namespace Deluxetech\LaRepo\Filters\Traits;

trait SanitizesArrayOfScalarValues
{
    use SanitizesScalarValue;

    /**
     * Sanitize a value that should be an array.
     *
     * @param  mixed $value
     * @return array
     */
    protected function sanitizeArrayOfScalarValues(mixed $value): array
    {
        if (is_array($value)) {
            $value = array_values($value);

            foreach ($value as $i => $item) {
                $value[$i] = $this->sanitizeScalarValue($item);
            }

            return $value;
        } else {
            return [];
        }
    }
}
