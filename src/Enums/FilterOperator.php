<?php

namespace LaravelRepository\Enums;

class FilterOperator
{
    /**
     * Valid cases.
     *
     * @var string
     */
    public const OR = 'or';
    public const AND = 'and';

    /**
     * Get valid cases.
     *
     * @return array<string>
     */
    public static function cases(): array
    {
        return [
            self::OR,
            self::AND,
        ];
    }
}
