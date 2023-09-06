<?php

namespace Deluxetech\LaRepo;

use Deluxetech\LaRepo\Contracts\DataAttrContract;

class DataAttr implements DataAttrContract
{
    /**
     * The data attribute segments delimiter.
     *
     * @var string
     */
    public const DELIMITER = '.';

    /**
     * @var array<string>
     */
    protected array $segments;

    /**
     * @var bool
     */
    protected bool $isSegmented;

    /**
     * @var string
     */
    protected string $name;

    /**
     * @var string|null
     */
    protected ?string $exceptLastSegment;

    public function __construct(string ...$segments)
    {
        $this->setName(...$segments);
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function isSegmented(): bool
    {
        return $this->isSegmented;
    }

    public function countSegments(): int
    {
        return count($this->segments);
    }

    public function setName(string ...$segments): void
    {
        $this->segments = [];
        $this->addSegments(true, ...$segments);
    }

    public function addFromBeginning(string ...$segments): void
    {
        $this->addSegments(false, ...$segments);
    }

    public function removeFromBeginning(string ...$segments): void
    {
        foreach ($segments as $i => $segment) {
            if (
                !isset($this->segments[$i]) ||
                $this->segments[$i] !== $segment
            ) {
                return;
            }
        }

        $newSegments = array_slice($this->segments, $i + 1);
        $this->setName(...$newSegments);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getNameSegmented(): array
    {
        return $this->segments;
    }

    public function getNameFirstSegment(): ?string
    {
        return $this->segments[0] ?? null;
    }

    public function getNameLastSegment(): ?string
    {
        $i = count($this->segments) - 1;

        return $this->segments[$i] ?? null;
    }

    public function getNameExceptLastSegment(): ?string
    {
        return $this->exceptLastSegment;
    }

    /**
     * Adds segments to the attribute name.
     *
     * @param  bool $fromEnd
     * @param  string ...$segments
     * @return void
     */
    protected function addSegments(bool $fromEnd, string ...$segments): void
    {
        foreach ($segments as $segment) {
            if (str_contains($segment, self::DELIMITER)) {
                $segment = explode(self::DELIMITER, $segment);

                if ($fromEnd) {
                    $this->segments = [...$this->segments, ...$segment];
                } else {
                    $this->segments = [...$segment, ...$this->segments];
                }
            } elseif ($fromEnd) {
                $this->segments[] = $segment;
            } else {
                array_unshift($this->segments, $segment);
            }
        }

        $segmentsCount = count($this->segments);
        $this->isSegmented = $segmentsCount > 1;
        $this->name = join(self::DELIMITER, $this->segments);

        if ($segmentsCount) {
            $lastSegmentLen = strlen($this->segments[$segmentsCount - 1]) + 1;
            $this->exceptLastSegment = substr($this->name, 0, -$lastSegmentLen) ?: null;
        } else {
            $this->exceptLastSegment = null;
        }
    }
}
