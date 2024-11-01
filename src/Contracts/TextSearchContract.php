<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Contracts;

interface TextSearchContract
{
    public function __construct(string $text, DataAttrContract ...$attrs);

    public function getText(): string;

    public function setText(string $text): static;

    /**
     * @return array<DataAttrContract>
     */
    public function getAttrs(): array;

    public function setAttrs(DataAttrContract ...$attrs): static;
}
