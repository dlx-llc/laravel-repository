<?php

namespace Deluxetech\LaRepo\Contracts;

interface DataAttrContract
{
    /**
     * Class constructor.
     *
     * @param  string ...$segments
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
     * Returns the number of segments.
     *
     * @return int
     */
    public function countSegments(): int;

    /**
     * Sets the attribute name.
     *
     * @param  string ...$segments
     * @return void
     */
    public function setName(string ...$segments): void;

    /**
     * Adds name segments from the beginning of the attribute.
     *
     * @param  string ...$segments
     * @return void
     */
    public function addFromBeginning(string ...$segments): void;

    /**
     * Removes name segments from the beginning of the attribute.
     *
     * @param  string ...$segments
     * @return void
     */
    public function removeFromBeginning(string ...$segments): void;

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
     * Returns only the first segment of the attribute.
     *
     * @return string|null
     */
    public function getNameFirstSegment(): ?string;

    /**
     * Returns only the last segment of the attribute.
     *
     * @return string|null
     */
    public function getNameLastSegment(): ?string;

    /**
     * Returns the attribute name without the last segment.
     *
     * @return string|null
     */
    public function getNameExceptLastSegment(): ?string;
}
