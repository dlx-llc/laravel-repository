<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Enums;

class SortingDirection
{
    public const ASC = 'asc';
    public const DESC = 'desc';

    /**
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
