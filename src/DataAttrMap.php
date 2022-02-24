<?php

namespace LaravelRepository;

class DataAttrMap
{
    /**
     * Represents a map of data public attributes to the internal attributes.
     *
     * @var array
     */
    protected array $map = [];

    /**
     * Creates an instance of this class.
     *
     * @return static
     */
    public static function make(): static
    {
        return new static();
    }

    /**
     * Returns the attributes map.
     *
     * @return array
     */
    public function get(): array
    {
        return $this->map;
    }

    /**
     * Sets a link between public and internal attributes.
     *
     * @param  string $publicAttr
     * @param  string $internalAttr
     * @return static
     */
    public function set(string $publicAttr, string $internalAttr): static
    {
        $this->map[$publicAttr] = $internalAttr;

        return $this;
    }

    /**
     * Merges the given data attributes map with the current one.
     *
     * @param  DataAttrMap|null $map
     * @param  string|null $prefix
     * @return static
     */
    public function merge(?DataAttrMap $map, ?string $prefix = null): static
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

    /**
     * Returns the corresponding internal attribute of the given public attribute.
     * If there's no match, the given public attribute will be returned.
     *
     * @param  string $publicAttr
     * @return string
     */
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
