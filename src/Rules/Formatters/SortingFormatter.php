<?php

namespace Deluxetech\LaRepo\Rules\Formatters;

use Deluxetech\LaRepo\Enums\SortingDirection;
use Deluxetech\LaRepo\Contracts\SortingContract;
use Deluxetech\LaRepo\Contracts\SortingFormatterContract;

class SortingFormatter implements SortingFormatterContract
{
    /** @inheritdoc */
    public function parse(string $str): ?array
    {
        $dirs = join('|', SortingDirection::cases());
        $regex = "/^((?:[a-zA-Z_]\w*\.)*[a-zA-Z_]\w*)\,({$dirs})$/";

        if (!preg_match($regex, $str, $matches)) {
            return null;
        }

        $attr = $matches[1];
        $dir = $matches[2];

        return [$attr, $dir];
    }

    /** @inheritdoc */
    public function stringify(SortingContract $sorting): string
    {
        $attr = $sorting->getAttr()->getName();
        $dir = $sorting->getDir();

        return "$attr,$dir";
    }
}
