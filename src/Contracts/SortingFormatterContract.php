<?php

namespace Deluxetech\LaRepo\Contracts;

interface SortingFormatterContract
{
    /**
     * Converts sorting raw string. Returns an array of parameters to make a
     * sorting object.
     *
     * @return array|null
     */
    public function parse(string $str): ?array;

    /**
     * Converts sorting to a raw string.
     *
     * @param  SortingContract $sorting
     * @return string
     */
    public function stringify(SortingContract $sorting): string;
}
