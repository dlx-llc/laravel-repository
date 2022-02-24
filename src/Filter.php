<?php

namespace LaravelRepository;

abstract class Filter
{
    /**
     * Indicates the conditional operator which will be used to apply the filter on the query.
     *
     * @var bool
     */
    public bool $orCond = false;

    /**
     * The filter data attribute relation.
     *
     * @var string|null
     */
    public ?string $relation = null;

    /**
     * The filter data attribute.
     *
     * @var string|null
     */
    public ?string $attr = null;

    /**
     * The filter value.
     *
     * @var mixed
     */
    public mixed $value = null;

    /**
     * Make an instance of this class.
     *
     * @param  string|null $attr
     * @param  mixed $value
     * @param  bool $orCond
     * @return static
     */
    public static function make(?string $attr, mixed $value, bool $orCond): static
    {
        return new static($attr, $value, $orCond);
    }

    /**
     * Validates the filter value.
     *
     * @param  string $attribute  The name of the attribute under validation.
     * @param  mixed $value  The value to validate.
     * @return array<string>  The error messages array.
     */
    abstract public static function validateValue(string $attribute, mixed $value): array;

    /**
     * Constructor.
     *
     * @param  string|null $attr
     * @param  mixed $value
     * @param  bool $orCond
     * @return void
     */
    public function __construct(?string $attr, mixed $value, bool $orCond)
    {
        $this->orCond = $orCond;
        $this->setAttr($attr);
        $this->value = $this->sanitizeValue($value);
    }

    /**
     * Gets the data attribute name with the relation.
     *
     * @return string
     */
    public function getAttr(): string
    {
        return $this->relation
            ? $this->relation . '.' . $this->attr
            : $this->attr;
    }

    /**
     * Sets the filter data attribute.
     *
     * @param  string $attr
     * @return static
     */
    public function setAttr(string $attr): static
    {
        if (str_contains($attr, '.')) {
            $lastDotPos = strrpos($attr, '.');
            $this->relation = substr($attr, 0, $lastDotPos);
            $this->attr = substr($attr, $lastDotPos + 1);
        } else {
            $this->attr = $attr;
        }

        return $this;
    }

    /**
     * Sanitizes the filter value.
     *
     * @param  mixed $value
     * @return mixed
     */
    abstract protected function sanitizeValue(mixed $value): mixed;
}
