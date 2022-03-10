<?php

namespace Deluxetech\LaRepo;

use Illuminate\Support\Collection;

abstract class DomainMapper
{
    /**
     * Domain model to source data map.
     *
     * @var DomainMap
     */
    private DomainMap $domainMap;

    /**
     * Creates the domain model to source data map.
     *
     * @return DomainMap
     */
    abstract protected function createDomainMap(): DomainMap;

    /**
     * Class constructor.
     *
     * @return void
     */
    final public function __construct()
    {
        $this->domainMap = $this->createDomainMap();
    }

    /**
     * Returns the domain model class name.
     *
     * @return string
     */
    final public function getDomainModel(): string
    {
        return $this->domainMap->getDomainModel();
    }

    /**
     * Returns the data source.
     *
     * @return string
     */
    final public function getDataSource(): string
    {
        return $this->domainMap->getDataSource();
    }

    /**
     * Returns the source data attributes that should be loaded.
     *
     * @return array<string>
     */
    final public function getSourceDataAttrs(): array
    {
        $result = [];

        foreach ($this->domainMap->getAttrMaps() as $attrMap) {
            if ($dataAttr = $attrMap->getDataAttr()) {
                $result[] = $dataAttr;
            }
        }

        return $result;
    }

    /**
     * Creates a domain object from the given source data.
     *
     * @param  object $sourceData
     * @return object
     */
    final public function makeDomainObject(object $sourceData): object
    {
        $domainClass = $this->domainMap->getDomainModel();
        $object = new $$domainClass();

        foreach ($this->domainMap->getAttrMaps() as $attrMap) {
            $dataAttr = $attrMap->getDataAttr();

            if ($resolver = $attrMap->getResolver()) {
                $val = call_user_func_array($resolver, [$sourceData, $dataAttr]);
            } elseif ($dataAttr) {
                $val = $sourceData->{$dataAttr} ?? null;
            } else {
                $val = null;
            }

            $object->{$attrMap->getDomainAttr()} = $val;
        }

        return $object;
    }

    /**
     * Creates a domain object from the given source data collection.
     *
     * @param  Collection $collection
     * @return Collection
     */
    final public function collectDomainObjects(Collection $collection): Collection
    {
        $result = Collection::make();

        foreach ($collection as $sourceData) {
            $domainModel = $this->makeDomainObject($sourceData);
            $result->add($domainModel);
        }

        return $result;
    }
}
