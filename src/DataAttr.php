<?php

namespace Deluxetech\LaRepo;

use Deluxetech\LaRepo\Contracts\DataAttrContract;

class DataAttr implements DataAttrContract
{
    /**
     * @var string
     */
    protected string $name;

    /**
     * @var string|null
     */
    protected ?string $relation = null;

    /** @inheritdoc */
    public function __construct(string $name)
    {
        $this->setName($name);
    }

    /** @inheritdoc */
    public function __toString(): string
    {
        return $this->getNameWithRelation();
    }

    /** @inheritdoc */
    public function setName(string $name): void
    {
        if (str_contains($name, '.')) {
            $lastDotPos = strrpos($name, '.');
            $this->relation = substr($name, 0, $lastDotPos);
            $this->name = substr($name, $lastDotPos + 1);
        } else {
            $this->relation = null;
            $this->name = $name;
        }
    }

    /** @inheritdoc */
    public function getRelation(): ?string
    {
        return $this->relation;
    }

    /** @inheritdoc */
    public function getName(): string
    {
        return $this->name;
    }

    /** @inheritdoc */
    public function getNameWithRelation(): string
    {
        return $this->relation ? "{$this->relation}.{$this->name}" : $this->name;
    }
}
