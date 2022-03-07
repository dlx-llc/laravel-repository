<?php

namespace Deluxetech\LaRepo\Traits;

use Illuminate\Support\Facades\App;
use Deluxetech\LaRepo\Contracts\SortingContract;
use Deluxetech\LaRepo\Contracts\DataAttrContract;
use Deluxetech\LaRepo\Contracts\SortingFormatterContract;

trait SupportsSorting
{
    /**
     * The data sorting params.
     *
     * @var SortingContract|null
     */
    protected ?SortingContract $sorting = null;

    /** @inheritdoc */
    public function getSorting(): ?SortingContract
    {
        return $this->sorting;
    }

    /** @inheritdoc */
    public function setSortingRaw(string $rawStr): static
    {
        $params = App::make(SortingFormatterContract::class)->parse($rawStr);

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

    /** @inheritdoc */
    public function setSorting(?SortingContract $sorting): static
    {
        $this->sorting = $sorting;

        return $this;
    }
}
