<?php

namespace LaravelRepository\Traits;

use LaravelRepository\Sorting;
use LaravelRepository\Enums\SortingDirection;

trait SupportsSorting
{
    /**
     * The data sorting params.
     *
     * @var Sorting|null
     */
    public ?Sorting $sorting = null;

    /**
     * Parses data sorting raw string params.
     *
     * @param  string $rawStr
     * @return array|null
     */
    public static function parseSortingStr(string $rawStr): ?array
    {
        $dirs = join('|', SortingDirection::cases());
        $regex = "/^((?:[a-zA-Z_]\w*\.)*[a-zA-Z_]\w*)\,({$dirs})$/";

        if (!preg_match($regex, $rawStr, $matches)) {
            return null;
        }

        $attr = $matches[1];
        $dir = $matches[2];

        return [$attr, $dir];
    }

    /**
     * Sets data sorting params from the given raw sorting string.
     *
     * @param  string $rawStr
     * @return static
     * @throws \Exception
     */
    public function setSortingRaw(string $rawStr): static
    {
        $params = static::parseSortingStr($rawStr);

        if (!$params) {
            throw new \Exception(__('lrepo::exceptions.invalid_sorting_string'));
        }

        $this->sorting = Sorting::make($params[0], $params[1]);

        return $this;
    }
}
