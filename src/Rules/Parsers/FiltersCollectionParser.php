<?php

namespace LaravelRepository\Rules\Parsers;

use LaravelRepository\Contracts\FiltersCollectionParserContract;

class FiltersCollectionParser implements FiltersCollectionParserContract
{
    /** @inheritdoc */
    public function parse(string $str): ?array
    {
        return json_decode($str, true);
    }
}
