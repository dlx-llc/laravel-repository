<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Contracts;

interface DataAttrContract
{
    public function __construct(string ...$segments);

    /**
     * Returns a concatenated string of the attribute segments.
     */
    public function __toString(): string;

    /**
     * Indicates whether or not the attribute name is segmented.
     */
    public function isSegmented(): bool;

    /**
     * Returns the number of segments.
     */
    public function countSegments(): int;

    /**
     * Sets the attribute name.
     */
    public function setName(string ...$segments): void;

    /**
     * Adds name segments from the beginning of the attribute.
     */
    public function addFromBeginning(string ...$segments): void;

    /**
     * Removes name segments from the beginning of the attribute.
     */
    public function removeFromBeginning(string ...$segments): void;

    /**
     * Returns the attribute name with all segments combined.
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
     */
    public function getNameFirstSegment(): ?string;

    /**
     * Returns only the last segment of the attribute.
     */
    public function getNameLastSegment(): ?string;

    /**
     * Returns the attribute name without the last segment.
     */
    public function getNameExceptLastSegment(): ?string;
}
