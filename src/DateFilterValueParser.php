<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo;

use Illuminate\Support\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Deluxetech\LaRepo\Exceptions\InvalidFilterValueException;

/**
 * Converts a date(time) filter value to a standard format that can be handled by the repository query engine.
 * Should be used to avoid unexpected behavior when filtering by non-standard date(time) formats.
 * Handles either a single date and an array of dates.
 */
class DateFilterValueParser
{
    private const DATE_FORMAT = 'Y-m-d';
    private const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * @return string|array<string>
     * @throws InvalidFilterValueException
     */
    public static function parseDate(mixed $value, string $attr): string|array
    {
        return is_array($value)
            ? array_map(static fn ($v) => self::parse($v, $attr, false), $value)
            : self::parse($value, $attr, false);
    }

    /**
     * @return string|array<string>
     * @throws InvalidFilterValueException
     */
    public static function parseDatetime(mixed $value, string $attr): string|array
    {
        return is_array($value)
            ? array_map(static fn ($v) => self::parse($v, $attr, true), $value)
            : self::parse($value, $attr, true);
    }

    /**
     * Converts the given value to a standard date(time) format.
     *
     * @throws InvalidFilterValueException
     */
    private static function parse(mixed $value, string $attr, bool $withTime): string
    {
        if (!is_string($value) || !$value) {
            throw new InvalidFilterValueException($attr);
        }

        $format = $withTime ? self::DATETIME_FORMAT : self::DATE_FORMAT;

        try {
            return Carbon::parse($value)->format($format);
        } catch (InvalidFormatException $exp) {
            throw new InvalidFilterValueException($attr, $exp);
        }
    }
}
