<?php

namespace LaravelRepository\Contracts;

use LaravelRepository\Contracts\DataAttrContract;

interface TextSearchContract
{
    /**
     * Class constructor.
     *
     * @param  string $text
     * @param  DataAttrContract ...$attrs
     * @return void
     */
    public function __construct(string $text, DataAttrContract ...$attrs);

    /**
     * Returns the search text.
     *
     * @return string
     */
    public function getText(): string;

    /**
     * Specifies the search text.
     *
     * @param  string $text
     * @return static
     */
    public function setText(string $text): static;

    /**
     * Returns data attributes for the search.
     *
     * @return array<DataAttrContract>
     */
    public function getAttrs(): array;

    /**
     * Specifies the data attributes for the search.
     *
     * @param  DataAttrContract ...$attrs
     * @return static
     */
    public function setAttrs(DataAttrContract ...$attrs): static;
}
