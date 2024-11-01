<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Contracts;

use Deluxetech\LaRepo\Enums\BooleanOperator;

/**
 * @template TValue
 */
interface FilterContract
{
    public function __construct(
        DataAttrContract $attr,
        string $operator,
        mixed $value,
        string $boolean = BooleanOperator::AND,
    );

    /**
     * @param string $attribute  The name of the attribute under validation.
     * @param mixed $value  The value of the filter.
     * @return array<string>  The error messages array.
     */
    public static function validateValue(string $attribute, mixed $value): array;

    public function getAttr(): DataAttrContract;

    public function setAttr(DataAttrContract $attr): static;

    public function getOperator(): string;

    public function hasValue(): bool;

    /**
     * @return TValue
     */
    public function getValue(): mixed;

    /**
     * @param TValue $value
     */
    public function setValue(mixed $value): static;

    public function getBoolean(): string;

    public function setBoolean(string $boolean): static;

    public function clone(): static;
}
