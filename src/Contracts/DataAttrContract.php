<?php

namespace Deluxetech\LaRepo\Contracts;

interface DataAttrContract
{
    /**
     * Class constructor.
     *
     * @param  string $name
     * @return void
     */
    public function __construct(string $name);

    /**
     * Converts the object to string.
     *
     * @return string
     */
    public function __toString(): string;

    /**
     * Sets the attribute name.
     *
     * @param  string $name
     * @return void
     */
    public function setName(string $name): void;

    /**
     * Returns the attribute's relation.
     *
     * @return string|null
     */
    public function getRelation(): ?string;

    /**
     * Returns the attribute name without the relation name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Returns the attribute name including the relation name.
     *
     * @return string
     */
    public function getNameWithRelation(): string;
}
