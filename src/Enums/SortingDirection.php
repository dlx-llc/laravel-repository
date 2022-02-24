<?php

namespace LaravelRepository\Enums;

class SortingDirection
{
    /**
     * Sorting directions.
     *
     * @var string
     */
    const ASC = 'asc';
    const DESC = 'desc';

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
