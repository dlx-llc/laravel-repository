<?php

namespace LaravelRepository\Contracts;

interface TextSearchFormatterContract
{
    /**
     * Converts test search raw string. Returns an array of parameters to make
     * a test search object.
     *
     * @return array|null
     */
    public function parse(string $str): ?array;

    /**
     * Converts test search to a raw string.
     *
     * @param  TextSearchContract $testSearch
     * @return string
     */
    public function stringify(TextSearchContract $testSearch): string;
}
