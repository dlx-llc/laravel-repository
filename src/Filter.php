<?php

namespace Deluxetech\LaRepo;

use Deluxetech\LaRepo\Enums\FilterOperator;
use Deluxetech\LaRepo\Contracts\FilterContract;
use Deluxetech\LaRepo\Contracts\DataAttrContract;

abstract class Filter implements FilterContract
{
    /**
     * Sanitizes the filter value.
     *
     * @param  mixed $value
     * @return mixed
     */
    abstract protected function sanitizeValue(mixed $value): mixed;

    /** @inheritdoc */
    public function __construct(
        protected DataAttrContract $attr,
        protected string $mode,
        protected mixed $value,
        protected string $operator = FilterOperator::AND
    ) {
        $this->value = $this->sanitizeValue($value);
    }

    /** @inheritdoc */
    public function getAttr(): DataAttrContract
    {
        return $this->attr;
    }

    /** @inheritdoc */
    public function setAttr(DataAttrContract $attr): static
    {
        $this->attr = $attr;

        return $this;
    }

    /** @inheritdoc */
    public function getMode(): string
    {
        return $this->mode;
    }

    /** @inheritdoc */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /** @inheritdoc */
    public function setValue(mixed $value): static
    {
        $this->value = $this->sanitizeValue($value);

        return $this;
    }

    /** @inheritdoc */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /** @inheritdoc */
    public function setOperator(string $operator): static
    {
        $this->operator = $operator;

        return $this;
    }
}
