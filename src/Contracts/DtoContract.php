<?php

namespace LaravelRepository\Contracts;

use LaravelRepository\Contracts\DtoAttrMapContract;

/**
 * Implement this interface in your data transfer objects, like resources in
 * Laravel. That, alongside the FetchesRepositoryData trait, will let you
 * easily transfer data from your API to clients and free you from a lot of
 * repetitive work.
 */
interface DtoContract
{
    /**
     * Returns the data public attributes to the internal attributes map.
     *
     * @return DtoAttrMapContract|null
     */
    public static function attrMap(): ?DtoAttrMapContract;

    /**
     * Returns the relations that are used in the data transfer object.
     *
     * @return array[string => DtoContract]
     */
    public static function usedRelations(): array;

    /**
     * Returns the relation counts that are used in the data transfer object.
     *
     * @return array[string, string => \Closure]
     */
    public static function usedRelationCounts(): array;
}
