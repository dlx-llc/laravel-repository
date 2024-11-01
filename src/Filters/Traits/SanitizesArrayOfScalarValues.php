<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Filters\Traits;

trait SanitizesArrayOfScalarValues
{
    use SanitizesScalarValue;

    /**
     * @return array<bool|int|float|string>
     */
    protected function sanitizeArrayOfScalarValues(mixed $value): array
    {
        if (is_array($value)) {
            $value = array_values($value);

            foreach ($value as $i => $item) {
                $value[$i] = $this->sanitizeScalarValue($item);
            }

            return $value;
        }

        return [];
    }
}
