<?php

namespace LaravelRepository\Enums;

class SortingDirection
{
    /**
     * Valid cases.
     *
     * @var string
     */
    public const ASC = 'asc';
    public const DESC = 'desc';

    /**
     * Get valid cases.
     *
     * @return array<string>
     */
    public static function cases(): array
    {
        return [
            self::ASC,
            self::DESC,
        ];
    }
}
