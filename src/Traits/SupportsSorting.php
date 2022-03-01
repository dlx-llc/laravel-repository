<?php

namespace LaravelRepository\Traits;

use Illuminate\Support\Facades\App;
use LaravelRepository\Enums\SortingDirection;
use LaravelRepository\Contracts\SortingContract;
use LaravelRepository\Contracts\DataAttrContract;

trait SupportsSorting
{
    /**
     * The data sorting params.
     *
     * @var SortingContract|null
     */
    public ?SortingContract $sorting = null;

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

        $attr = App::makeWith(DataAttrContract::class, ['name' => $params[0]]);
        $this->sorting = App::makeWith(SortingContract::class, [
            'attr' => $attr,
            'dir' => $params[1],
        ]);

        return $this;
    }
}
