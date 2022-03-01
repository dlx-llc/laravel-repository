<?php

namespace LaravelRepository;

class DataAttr
{
    /**
     * @var string
     */
    protected string $name;

    /**
     * @var string|null
     */
    protected ?string $relation = null;

    /**
     * Class constructor.
     *
     * @param  string $name
     * @return void
     */
    public function __construct(string $name)
    {
        $this->setName($name);
    }

    /**
     * Sets the attribute name.
     *
     * @param  string $name
     * @return void
     */
    public function setName(string $name): void
    {
        if (str_contains($name, '.')) {
            $lastDotPos = strrpos($name, '.');
            $this->relation = substr($name, 0, $lastDotPos);
            $this->name = substr($name, $lastDotPos + 1);
        } else {
            $this->name = $name;
        }
    }

    /**
     * Returns the attribute's relation.
     *
     * @return string|null
     */
    public function getRelation(): ?string
    {
        return $this->relation;
    }

    /**
     * Returns the attribute name without the relation name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the attribute name including the relation name.
     *
     * @return string
     */
    public function getNameWithRelation(): string
    {
        return $this->relation ? "{$this->relation}.{$this->name}" : $this->name;
    }

    /**
     * Converts the object to string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->getNameWithRelation();
    }
}
