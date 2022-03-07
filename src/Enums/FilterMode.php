<?php

namespace Deluxetech\LaRepo\Enums;

class FilterMode
{
    /**
     * Valid cases.
     *
     * @var string
     */
    public const IS_LIKE = 'like';
    public const IS_NOT_LIKE = '!like';
    public const EQUALS_TO = '=';
    public const NOT_EQUALS_TO = '!=';
    public const INCLUDED_IN = 'included';
    public const NOT_INCLUDED_IN = '!included';
    public const CONTAINS = 'contains';
    public const DOES_NOT_CONTAIN = '!contains';
    public const IN_RANGE = 'range';
    public const NOT_IN_RANGE = '!range';
    public const IS_GREATER = '>';
    public const IS_GREATER_OR_EQUAL = '>=';
    public const IS_LOWER = '<';
    public const IS_LOWER_OR_EQUAL = '<=';
    public const IS_NULL = 'null';
    public const IS_NOT_NULL = '!null';
    public const EXISTS = 'exists';
    public const DOES_NOT_EXIST = '!exists';

    /**
     * Get valid cases.
     *
     * @return array<string>
     */
    public static function cases(): array
    {
        return [
            self::IS_LIKE,
            self::IS_NOT_LIKE,
            self::EQUALS_TO,
            self::NOT_EQUALS_TO,
            self::INCLUDED_IN,
            self::NOT_INCLUDED_IN,
            self::CONTAINS,
            self::DOES_NOT_CONTAIN,
            self::IN_RANGE,
            self::NOT_IN_RANGE,
            self::IS_GREATER,
            self::IS_GREATER_OR_EQUAL,
            self::IS_LOWER,
            self::IS_LOWER_OR_EQUAL,
            self::IS_NULL,
            self::IS_NOT_NULL,
            self::EXISTS,
            self::DOES_NOT_EXIST,
        ];
    }
}
