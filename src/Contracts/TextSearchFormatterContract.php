<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Contracts;

interface TextSearchFormatterContract
{
    /**
     * Converts test search raw string. Returns an array of parameters to make
     * a test search object.
     *
     * @return ?array<mixed>
     */
    public function parse(string $str): ?array;

    /**
     * Converts text search object to a raw string.
     */
    public function stringify(TextSearchContract $testSearch): string;
}
