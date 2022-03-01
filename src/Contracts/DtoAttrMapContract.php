<?php

namespace LaravelRepository\Contracts;

interface DtoAttrMapContract
{
    /**
     * Returns the attributes map.
     *
     * @return array
     */
    public function get(): array;

    /**
     * Sets a link between public and internal attributes.
     *
     * @param  string $publicAttr
     * @param  string $internalAttr
     * @return static
     */
    public function set(string $publicAttr, string $internalAttr): static;

    /**
     * Merges the given attributes map with the current one.
     *
     * @param  DtoAttrMapContract|null $map
     * @param  string|null $prefix
     * @return static
     */
    public function merge(?DtoAttrMapContract $map, ?string $prefix = null): static;

    /**
     * Returns the corresponding internal attribute of the given public attribute.
     * If there's no match, the given public attribute will be returned.
     *
     * @param  string $publicAttr
     * @return string
     */
    public function match(string $publicAttr): string;
}
