<?php

namespace LaravelRepository\Enums;

class FilterGroupMode
{
    /**
     * Filter group modes.
     *
     * @var string
     */
    public const HAS = 'has';
    public const DOES_NOT_HAVE = '!has';

    /**
     * Get valid cases.
     *
     * @return array<string>
     */
    public static function cases(): array
    {
        return [
            self::HAS,
            self::DOES_NOT_HAVE,
        ];
    }
}
