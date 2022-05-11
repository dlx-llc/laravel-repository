<?php

namespace Deluxetech\LaRepo\Traits;

use Illuminate\Support\Facades\App;
use Deluxetech\LaRepo\Facades\LaRepo;
use Deluxetech\LaRepo\Contracts\TextSearchContract;
use Deluxetech\LaRepo\Contracts\TextSearchFormatterContract;

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
            throw new \Exception(__('larepo::exceptions.invalid_text_search_string'));
        }

        $textSearch = LaRepo::newTextSearch($params[0], ...$params[1]);
        $this->setTextSearch($textSearch);

        return $this;
    }

    /** @inheritdoc */
    public function setTextSearch(?TextSearchContract $textSearch): static
    {
        $this->textSearch = $textSearch;

        return $this;
    }
}
