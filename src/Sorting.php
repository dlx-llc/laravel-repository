<?php

namespace LaravelRepository;

class Sorting
{
    /**
     * The data attribute relation using which sorting should be done.
     *
     * @var string|null
     */
    public ?string $relation = null;

    /**
     * The data attribute using which sorting should be done.
     *
     * @var string|null
     */
    public string $attr;

    /**
     * Creates a new instance of this class.
     *
     * @param  string $attr
     * @param  string $dir
     * @return static
     */
    public static function make(string $attr, string $dir): static
    {
        return new static($attr, $dir);
    }

    /**
     * Constructor.
     *
     * @param  string $attr
     * @param  string $dir
     * @return void
     */
    public function __construct(string $attr, public string $dir)
    {
        $this->setAttr($attr);
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
     * Sets the sorting data attribute.
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
}
