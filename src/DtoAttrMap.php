<?php

namespace LaravelRepository;

use LaravelRepository\Contracts\DtoAttrMapContract;

class DtoAttrMap implements DtoAttrMapContract
{
    /**
     * Represents a map of public and internal DTO attributes.
     * The array keys are public attribute names, and values are the
     * corresponding internal attributes.
     *
     * @var array[string => string]
     */
    protected array $map = [];

    /** @inheritdoc */
    public function get(): array
    {
        return $this->map;
    }

    /** @inheritdoc */
    public function set(string $publicAttr, string $internalAttr): static
    {
        $this->map[$publicAttr] = $internalAttr;

        return $this;
    }

    /** @inheritdoc */
    public function merge(?DtoAttrMapContract $map, ?string $prefix = null): static
    {
        if ($map) {
            $map = $map->get();

            if ($prefix) {
                $publicPrefix = $prefix . '.';
                $internalPrefix = $this->match($prefix) . '.';
            } else {
                $publicPrefix = $internalPrefix = '';
            }

            foreach ($map as $publicAttr => $internalAttr) {
                $publicAttr = $publicPrefix . $publicAttr;
                $internalAttr = $internalPrefix . $internalAttr;
                $this->map[$publicAttr] = $internalAttr;
            }
        }

        return $this;
    }

    /** @inheritdoc */
    public function match(string $publicAttr): string
    {
        return $this->findMatch($publicAttr) ?? $publicAttr;
    }

    /**
     * Finds a match for the given public attribute.
     *
     * @param  string $publicAttr
     * @return string|null
     */
    protected function findMatch(string $publicAttr): ?string
    {
        // Returns the direct match if set.
        if (isset($this->map[$publicAttr])) {
            return $this->map[$publicAttr];
        }

        // When a multilevel attribute is given, looks for a match for each level.
        if (str_contains($publicAttr, '.')) {
            $lastDotPos = strrpos($publicAttr, '.');
            $relation = substr($publicAttr, 0, $lastDotPos);
            $attr = substr($publicAttr, $lastDotPos + 1);

            if ($match = $this->findMatch($relation)) {
                return $match . '.' . $attr;
            }
        }

        return null;
    }
}
