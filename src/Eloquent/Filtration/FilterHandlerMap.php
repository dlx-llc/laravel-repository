<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Eloquent\Filtration;

class FilterHandlerMap
{
    /**
     * Filter operators to handler functions map.
     *
     * @var array<string,FilterHandlerContract>
     */
    protected array $map = [];

    public function set(string $operator, FilterHandlerContract $handler): self
    {
        $this->map[$operator] = $handler;

        return $this;
    }

    public function get(string $operator): ?FilterHandlerContract
    {
        return $this->map[$operator] ?? null;
    }
}
