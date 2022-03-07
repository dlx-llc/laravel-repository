<?php

namespace Deluxetech\LaRepo\Rules\Formatters;

use Deluxetech\LaRepo\Contracts\TextSearchContract;
use Deluxetech\LaRepo\Contracts\TextSearchFormatterContract;

class TextSearchFormatter implements TextSearchFormatterContract
{
    /** @inheritdoc */
    public function parse(string $str): ?array
    {
        $regex = '/(?(DEFINE)(?<attr>(?:[a-zA-Z_]\w*\.)*[a-zA-Z_]\w*))^(.+)\,\[((?:(?P>attr)\,)*(?P>attr))\]$/';

        if (!preg_match($regex, $str, $matches)) {
            return null;
        }

        $text = $matches[2];
        $attrs = explode(',', $matches[3]);

        return [$text, $attrs];
    }

    /** @inheritdoc */
    public function stringify(TextSearchContract $textSearch): string
    {
        $text = $textSearch->getText();
        $attrs = $textSearch->getAttrs();

        foreach ($attrs as $i => $attr) {
            $attrs[$i] = $attr->getNameWithRelation();
        }

        $attrs = join(',', $attrs);

        return "$text,[$attrs]";
    }
}
