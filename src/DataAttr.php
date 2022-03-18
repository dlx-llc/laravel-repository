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
    const DELIMITER = '.';

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

    /**
     * @var string|null
     */
    protected ?string $lastSegment;

    /** @inheritdoc */
    public function __construct(string ...$segments)
    {
        $this->setName(...$segments);
    }

    /** @inheritdoc */
    public function __toString(): string
    {
        return $this->getName();
    }

    /** @inheritdoc */
    public function isSegmented(): bool
    {
        return $this->isSegmented;
    }

    /** @inheritdoc */
    public function setName(string ...$segments): void
    {
        $this->segments = [];
        $this->addSegments(true, ...$segments);
    }

    /** @inheritdoc */
    public function addFromBeginning(string ...$segments): void
    {
        $this->addSegments(false, ...$segments);
    }

    /** @inheritdoc */
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

    /** @inheritdoc */
    public function getName(): string
    {
        return $this->name;
    }

    /** @inheritdoc */
    public function getNameSegmented(): array
    {
        return $this->segments;
    }

    /** @inheritdoc */
    public function getNameLastSegment(): string
    {
        return $this->lastSegment;
    }

    /** @inheritdoc */
    public function getNameExceptLastSegment(): string
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
        $this->lastSegment = $this->segments[$segmentsCount - 1];
        $lastSegmentLen = strlen($this->lastSegment) + 1;
        $this->exceptLastSegment = substr($this->name, 0, -$lastSegmentLen);
    }
}
