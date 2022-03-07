<?php

namespace Deluxetech\LaRepo;

use Deluxetech\LaRepo\Contracts\SortingContract;
use Deluxetech\LaRepo\Contracts\DataAttrContract;

/**
 * Example: creator.id,asc
 */
class Sorting implements SortingContract
{
    /** @inheritdoc */
    public function __construct(
        protected DataAttrContract $attr,
        protected string $dir
    ) {
        //
    }

    /** @inheritdoc */
    public function getAttr(): DataAttrContract
    {
        return $this->attr;
    }

    /** @inheritdoc */
    public function setAttr(DataAttrContract $attr): static
    {
        $this->attr = $attr;

        return $this;
    }

    /** @inheritdoc */
    public function getDir(): string
    {
        return $this->dir;
    }

    /** @inheritdoc */
    public function setDir(string $dir): static
    {
        $this->dir = $dir;

        return $this;
    }
}
