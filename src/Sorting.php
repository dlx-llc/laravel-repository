<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo;

use Deluxetech\LaRepo\Contracts\SortingContract;
use Deluxetech\LaRepo\Contracts\DataAttrContract;

/**
 * Example: creator.id,asc
 */
class Sorting implements SortingContract
{
    public function __construct(
        protected DataAttrContract $attr,
        protected string $dir,
    ) {
    }

    public function getAttr(): DataAttrContract
    {
        return $this->attr;
    }

    public function setAttr(DataAttrContract $attr): static
    {
        $this->attr = $attr;

        return $this;
    }

    public function getDir(): string
    {
        return $this->dir;
    }

    public function setDir(string $dir): static
    {
        $this->dir = $dir;

        return $this;
    }
}
