<?php

namespace LaravelRepository\Traits;

use Illuminate\Support\Facades\App;
use LaravelRepository\Contracts\DataAttrContract;
use LaravelRepository\Contracts\TextSearchContract;
use LaravelRepository\Contracts\TextSearchFormatterContract;

trait SupportsTextSearch
{
    /**
     * The text search params.
     *
     * @var TextSearchContract|null
     */
    protected ?TextSearchContract $textSearch = null;

    /** @inheritdoc */
    public function getTextSearch(): ?TextSearchContract
    {
        return $this->textSearch;
    }

    /** @inheritdoc */
    public function setTextSearchRaw(string $rawStr): static
    {
        $params = App::make(TextSearchFormatterContract::class)->parse($rawStr);

        if (!$params) {
            throw new \Exception(__('lrepo::exceptions.invalid_text_search_string'));
        }

        foreach ($params[1] as $i => $attr) {
            $params[1][$i] = App::makeWith(DataAttrContract::class, ['name' => $attr]);
        }

        $params = [$params[0], ...$params[1]];
        $this->textSearch = App::makeWith(TextSearchContract::class, $params);

        return $this;
    }

    /** @inheritdoc */
    public function setTextSearch(?TextSearchContract $textSearch): static
    {
        $this->textSearch = $textSearch;

        return $this;
    }
}
