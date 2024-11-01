<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Enums;

class BooleanOperator
{
    public const OR = 'or';
    public const AND = 'and';

    /**
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
