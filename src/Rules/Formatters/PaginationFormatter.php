<?php

namespace Deluxetech\LaRepo\Rules\Formatters;

use Deluxetech\LaRepo\Contracts\PaginationContract;
use Deluxetech\LaRepo\Contracts\PaginationFormatterContract;

class PaginationFormatter implements PaginationFormatterContract
{
    /** @inheritdoc */
    public function parse(string $str): ?array
    {
        if (!preg_match('/^([1-9]\d*)\,([1-9]\d*)$/', $str, $matches)) {
            return null;
        }

        $page = intval($matches[1]);
        $perPage = intval($matches[2]);

        return [$page, $perPage];
    }

    /** @inheritdoc */
    public function stringify(PaginationContract $pagination): string
    {
        $page = $pagination->getPage();
        $perPage = $pagination->getPerPage();

        return "$page,$perPage";
    }
}
