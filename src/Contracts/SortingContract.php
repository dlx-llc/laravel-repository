<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Contracts;

interface SortingContract
{
    public function __construct(DataAttrContract $attr, string $dir);

    public function getAttr(): DataAttrContract;

    public function setAttr(DataAttrContract $attr): static;

    public function getDir(): string;

    public function setDir(string $dir): static;
}
