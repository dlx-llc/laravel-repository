<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Filters\Traits;

trait SanitizesScalarValue
{
    protected function sanitizeScalarValue(mixed $value): bool|int|float|string
    {
        if (is_scalar($value)) {
            return $value;
        } elseif (is_object($value) && method_exists($value, '__toString')) {
            return $value->__toString();
        } else {
            return '';
        }
    }
}
