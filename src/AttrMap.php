<?php

namespace Deluxetech\LaRepo;

final class AttrMap
{
    /**
     * A custom resolver function for the domain model attribute value.
     * The function should accept the DB result object as its first parameter
     * and the column name as the second one.
     *
     * @var callable|null
     */
    private $resolver = null;

    /**
     * Class constructor.
     *
     * @param  string $domainAttr  The domain model attribute name.
     * @param  string|null $dataAttr  The source data attribute name.
     * @param  callable|null $resolver  The domain model attribute value custom resolver.
     * @return void
     */
    public function __construct(
        private string $domainAttr,
        private ?string $dataAttr,
        ?callable $resolver = null
    ) {
        $this->resolver = $resolver;
    }

    /**
     * Returns the domain model attribute name.
     *
     * @return string
     */
    public function getDomainAttr(): string
    {
        return $this->domainAttr;
    }

    /**
     * Returns the source data attribute name.
     *
     * @return string
     */
    public function getDataAttr(): string
    {
        return $this->dataAttr;
    }

    /**
     * Returns the domain model attribute value custom resolver.
     *
     * @return callable|null
     */
    public function getResolver(): ?callable
    {
        return $this->resolver;
    }
}
