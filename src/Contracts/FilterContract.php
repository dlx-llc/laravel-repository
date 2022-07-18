<?php

namespace Deluxetech\LaRepo\Contracts;

use Deluxetech\LaRepo\Enums\BooleanOperator;
use Deluxetech\LaRepo\Contracts\DataAttrContract;

interface FilterContract
{
    /**
     * Validates the filter value.
     *
     * @param  string $attribute  The name of the attribute under validation.
     * @param  mixed $value  The value to validate.
     * @return array<string>  The error messages array.
     */
    public static function validateValue(string $attribute, mixed $value): array;

    /**
     * Class constructor.
     *
     * @param  DataAttrContract $attr  Data attribute to filter.
     * @param  string $operator  The filter operator.
     * @param  mixed $value  The filter value.
     * @param  string $boolean  The boolean operator by which the filter will be combined with others.
     */
    public function __construct(
        DataAttrContract $attr,
        string $operator,
        mixed $value,
        string $boolean = BooleanOperator::AND
    );

    /**
     * Returns the filter data attribute.
     *
     * @return DataAttrContract
     */
    public function getAttr(): DataAttrContract;

    /**
     * Specifies the filter data attribute.
     *
     * @param  DataAttrContract $attr
     * @return static
     */
    public function setAttr(DataAttrContract $attr): static;

    /**
     * Returns the filter operator.
     *
     * @return string
     * @see \Deluxetech\LaRepo\Enums\FilterOperator
     */
    public function getOperator(): string;

    /**
     * Returns the filter value.
     *
     * @return mixed
     */
    public function getValue(): mixed;

    /**
     * Specifies the filter value.
     *
     * @param  mixed $value
     * @return static
     */
    public function setValue(mixed $value): static;

    /**
     * Returns the boolean operator by which the filter will be combined with others.
     *
     * @return string
     */
    public function getBoolean(): string;

    /**
     * Specifies the boolean operator by which the filter will be combined with others.
     *
     * @param  string $boolean
     * @return static
     */
    public function setBoolean(string $boolean): static;
}
