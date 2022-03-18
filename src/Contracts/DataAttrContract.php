<?php

namespace Deluxetech\LaRepo\Contracts;

interface DataAttrContract
{
    /**
     * Class constructor.
     *
     * @param  string ...$segments
     * @return void
     */
    public function __construct(string ...$segments);

    /**
     * Converts the object to string.
     *
     * @return string
     */
    public function __toString(): string;

    /**
     * Indicates whether or not the attribute name is segmented.
     *
     * @return bool
     */
    public function isSegmented(): bool;

    /**
     * Sets the attribute name.
     *
     * @param  string ...$segments
     * @return void
     */
    public function setName(string ...$segments): void;

    /**
     * Returns the attribute name with all segments combined.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Returns the attribute name segments.
     *
     * @return array<string>
     */
    public function getNameSegmented(): array;

    /**
     * Returns only the last segment of the attribute.
     *
     * @return string
     */
    public function getNameLastSegment(): string;

    /**
     * Returns the attribute name without the last segment.
     *
     * @return string
     */
    public function getNameExceptLastSegment(): string;
}
