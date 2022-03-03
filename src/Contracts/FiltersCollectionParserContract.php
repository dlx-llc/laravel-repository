<?php

namespace LaravelRepository\Contracts;

interface FiltersCollectionParserContract
{
    /**
     * Parses filters collection raw string. Returns an array of parameters
     * to make a filters collection.
     *
     * @return array|null
     */
    public function parse(string $str): ?array;
}
