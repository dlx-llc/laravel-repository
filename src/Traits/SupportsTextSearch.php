<?php

namespace LaravelRepository\Traits;

use LaravelRepository\TextSearch;

trait SupportsTextSearch
{
    /**
     * The text search params.
     *
     * @var TextSearch|null
     */
    public ?TextSearch $textSearch = null;

    /**
     * Parses text search raw string params.
     *
     * @param  string $rawStr
     * @return array|null
     */
    public static function parseTextSearchStr(string $rawStr): ?array
    {
        $regex = '/(?(DEFINE)(?<attr>(?:[a-zA-Z_]\w*\.)*[a-zA-Z_]\w*))^(.+)\,\[((?:(?P>attr)\,)*(?P>attr))\]$/';

        if (!preg_match($regex, $rawStr, $matches)) {
            return null;
        }

        $text = $matches[2];
        $attrs = explode(',', $matches[3]);

        return [$text, $attrs];
    }

    /**
     * Sets text search params from the given raw search string.
     *
     * @param  string $rawStr
     * @return static
     * @throws \Exception
     */
    public function setSearchRaw(string $rawStr): static
    {
        $params = static::parseTextSearchStr($rawStr);

        if (!$params) {
            throw new \Exception(__('lrepo::exceptions.invalid_text_search_string'));
        }

        $this->textSearch = TextSearch::make($params[0], ...$params[1]);

        return $this;
    }
}
