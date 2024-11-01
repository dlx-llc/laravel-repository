<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Contracts;

interface SortingFormatterContract
{
    /**
     * Converts sorting raw string. Returns an array of parameters to make a
     * sorting object.
     *
     * @return ?array<mixed>
     */
    public function parse(string $str): ?array;

    /**
     * Converts sorting object to a raw string.
     */
    public function stringify(SortingContract $sorting): string;
}
