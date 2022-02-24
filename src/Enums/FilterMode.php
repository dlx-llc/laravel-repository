<?php

namespace LaravelRepository\Enums;

class FilterMode
{
    /**
     * Filter modes.
     *
     * @var string
     */
    const IS_LIKE = 'like';
    const IS_NOT_LIKE = '!like';
    const EQUALS_TO = '=';
    const NOT_EQUALS_TO = '!=';
    const INCLUDED_IN = 'included';
    const NOT_INCLUDED_IN = '!included';
    const CONTAINS = 'contains';
    const DOES_NOT_CONTAIN = '!contains';
    const IN_RANGE = 'range';
    const NOT_IN_RANGE = '!range';
    const IS_GREATER = '>';
    const IS_GREATER_OR_EQUAL = '>=';
    const IS_LOWER = '<';
    const IS_LOWER_OR_EQUAL = '<=';
    const IS_NULL = 'null';
    const IS_NOT_NULL = '!null';

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
        ];
    }
}
