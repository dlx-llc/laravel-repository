<?php

namespace LaravelRepository\Contracts;

use LaravelRepository\Enums\FilterOperator;
use LaravelRepository\Contracts\DataAttrContract;

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
     * @param  mixed $value  The filter value.
     * @param  string $operator  The logical operator by which the filter will be combined with others.
     * @return void
     */
    public function __construct(
        DataAttrContract $attr,
        mixed $value,
        string $operator = FilterOperator::AND
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
     * Returns the logical operator by which the filter will be combined with others.
     *
     * @return string
     */
    public function getOperator(): string;

    /**
     * Specifies the logical operator by which the filter will be combined with others.
     *
     * @param  string $operator
     * @return static
     */
    public function setOperator(string $operator): static;
}
