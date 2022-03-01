<?php

namespace LaravelRepository\Contracts;

use LaravelRepository\Contracts\DataAttrContract;

interface SortingContract
{
    /**
     * Returns the data attribute for sorting.
     *
     * @return DataAttrContract
     */
    public function getAttr(): DataAttrContract;

    /**
     * Specifies the data attribute for sorting.
     *
     * @param  DataAttrContract $attr
     * @return static
     */
    public function setAttr(DataAttrContract $attr): static;

    /**
     * Returns the sorting direction.
     *
     * @return string
     */
    public function getDir(): string;

    /**
     * Specifies the sorting direction.
     *
     * @param  string $dir
     * @return static
     */
    public function setDir(string $dir): static;
}
