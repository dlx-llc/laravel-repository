<?php

namespace Deluxetech\LaRepo\Contracts;

interface PaginationFormatterContract
{
    /**
     * Converts pagination raw string. Returns an array of parameters to make a
     * pagination object.
     *
     * @return array|null
     */
    public function parse(string $str): ?array;

    /**
     * Converts pagination to a raw string.
     *
     * @param  PaginationContract $pagination
     * @return string
     */
    public function stringify(PaginationContract $pagination): string;
}
