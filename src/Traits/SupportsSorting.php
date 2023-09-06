<?php

namespace Deluxetech\LaRepo\Traits;

use Illuminate\Support\Facades\App;
use Deluxetech\LaRepo\Facades\LaRepo;
use Deluxetech\LaRepo\Contracts\SortingContract;
use Deluxetech\LaRepo\Contracts\SortingFormatterContract;

trait SupportsSorting
{
    /**
     * The data sorting params.
     *
     * @var SortingContract|null
     */
    protected ?SortingContract $sorting = null;

    public function getSorting(): ?SortingContract
    {
        return $this->sorting;
    }

    public function setSortingRaw(string $rawStr): static
    {
        $params = App::make(SortingFormatterContract::class)->parse($rawStr);

        if (!$params) {
            throw new \Exception(__('larepo::exceptions.invalid_sorting_string'));
        }

        $sorting = LaRepo::newSorting($params[0], $params[1]);
        $this->setSorting($sorting);

        return $this;
    }

    public function setSorting(?SortingContract $sorting): static
    {
        $this->sorting = $sorting;

        return $this;
    }
}
