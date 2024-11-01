<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Contracts;

interface FiltersCollectionFormatterContract
{
    /**
     * Converts filters collection raw string. Returns an array of parameters
     * to make a filters collection object.
     *
     * @return ?array<mixed>
     */
    public function parse(string $str): ?array;

    /**
     * Converts filters collection to a raw string.
     */
    public function stringify(FiltersCollectionContract $collection): string;
}
