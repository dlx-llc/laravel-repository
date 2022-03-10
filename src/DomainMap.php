<?php

namespace Deluxetech\LaRepo;

final class DomainMap
{
    /**
     * Domain model attributes to source data attributes map.
     *
     * @var array<AttrMap>
     */
    private array $attrMaps = [];

    /**
     * Class constructor.
     *
     * @param  string $domainModel  The domain model class name.
     * @param  string $dataSource  The data source.
     * @return void
     */
    public function __construct(
        private string $domainModel,
        private string $dataSource
    ) {
        RepositoryUtils::checkClassExists($domainModel);
    }

    /**
     * Returns the domain model class name.
     *
     * @return string
     */
    public function getDomainModel(): string
    {
        return $this->domainModel;
    }

    /**
     * Returns the data source.
     *
     * @return string
     */
    public function getDataSource(): string
    {
        return $this->dataSource;
    }

    /**
     * Returns domain to source data attribute maps.
     *
     * @return array<AttrMap>
     */
    public function getAttrMaps(): array
    {
        return $this->attrMaps;
    }

    /**
     * Adds domain to source data attribute map.
     *
     * @param  AttrMap $attrMap
     * @return void
     */
    public function addAttrMap(AttrMap $attrMap): void
    {
        $this->attrMaps[] = $attrMap;
    }
}
