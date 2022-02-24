<?php

namespace LaravelRepository;

interface DtoContract
{
    /**
     * Returns the data public attributes to the internal attributes map.
     *
     * @return DataAttrMap|null
     */
    public static function attrMap(): ?DataAttrMap;

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
