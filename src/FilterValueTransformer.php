<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo;

use Closure;
use Deluxetech\LaRepo\DateFilterValueParser;
use Deluxetech\LaRepo\Exceptions\InvalidFilterValueException;

class FilterValueTransformer
{
    /**
     * An attribute to the transformer function map.
     *
     * @var array<string,Closure>
     */
    private array $transformers = [];

    public function shouldBeDate(string $attr): self
    {
        $this->transformers[$attr] = DateFilterValueParser::parseDate(...);

        return $this;
    }

    public function shouldBeDatetime(string $attr): self
    {
        $this->transformers[$attr] = DateFilterValueParser::parseDatetime(...);

        return $this;
    }

    public function shouldUseTransformer(string $attr, Closure $transformer): self
    {
        $this->transformers[$attr] = $transformer;

        return $this;
    }

    public function shouldBeTransformed(string $attr): bool
    {
        return isset($this->transformers[$attr]);
    }

    /**
     * @throws InvalidFilterValueException
     */
    public function transform(string $attr, mixed $value): mixed
    {
        if (!$this->shouldBeTransformed($attr)) {
            return $value;
        }

        $transformer = $this->transformers[$attr];

        return $transformer($value, $attr);
    }
}
