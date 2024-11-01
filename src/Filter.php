<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo;

use Deluxetech\LaRepo\Facades\LaRepo;
use Deluxetech\LaRepo\Enums\BooleanOperator;
use Deluxetech\LaRepo\Contracts\FilterContract;
use Deluxetech\LaRepo\Contracts\DataAttrContract;

/**
 * @template TValue
 */
abstract class Filter implements FilterContract
{
    public function __construct(
        protected DataAttrContract $attr,
        protected string $operator,
        protected mixed $value,
        protected string $boolean = BooleanOperator::AND
    ) {
        $this->value = $this->sanitizeValue($value);
    }

    public function clone(): static
    {
        return LaRepo::newFilter(
            $this->getAttr()->getName(),
            $this->getOperator(),
            $this->getValue(),
            $this->getBoolean()
        );
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

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function hasValue(): bool
    {
        return true;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setValue(mixed $value): static
    {
        $this->value = $this->sanitizeValue($value);

        return $this;
    }

    public function getBoolean(): string
    {
        return $this->boolean;
    }

    public function setBoolean(string $boolean): static
    {
        $this->boolean = $boolean;

        return $this;
    }

    abstract protected function sanitizeValue(mixed $value): mixed;
}
