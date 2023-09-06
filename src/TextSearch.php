<?php

namespace Deluxetech\LaRepo;

use Deluxetech\LaRepo\Contracts\DataAttrContract;
use Deluxetech\LaRepo\Contracts\TextSearchContract;

/**
 * Example: John,[first_name,middle_name,last_name]
 */
class TextSearch implements TextSearchContract
{
    /**
     * @var array<DataAttrContract>
     */
    protected array $attrs = [];

    public function __construct(protected string $text, DataAttrContract ...$attrs)
    {
        $this->attrs = $attrs;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getAttrs(): array
    {
        return $this->attrs;
    }

    public function setAttrs(DataAttrContract ...$attrs): static
    {
        $this->attrs = $attrs;

        return $this;
    }
}
