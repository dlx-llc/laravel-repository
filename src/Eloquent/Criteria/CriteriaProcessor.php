<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Eloquent\Criteria;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Deluxetech\LaRepo\Contracts\CriteriaContract;
use Illuminate\Database\Eloquent\Relations\Relation;
use Deluxetech\LaRepo\Eloquent\Sorting\SortingProcessor;
use Deluxetech\LaRepo\Eloquent\Filtration\FiltersProcessor;
use Deluxetech\LaRepo\Eloquent\TextSearch\TextSearchProcessor;
use Deluxetech\LaRepo\Eloquent\Relationship\RelationshipResolverMap;
use Deluxetech\LaRepo\Eloquent\Relationship\RelationshipTransformerMap;
use Deluxetech\LaRepo\Eloquent\Relationship\EloquentRelationshipResolver;
use Deluxetech\LaRepo\Eloquent\Relationship\RelationshipCountResolverMap;
use Deluxetech\LaRepo\Eloquent\Relationship\EloquentRelationshipCountResolver;

class CriteriaProcessor
{
    public RelationshipResolverMap $relationshipResolverMap;
    public RelationshipCountResolverMap $relationshipCountResolverMap;
    public RelationshipTransformerMap $relationshipTransformerMap;

    public SortingProcessor $sortingProcessor;
    public TextSearchProcessor $textSearchProcessor;
    public FiltersProcessor $filtersProcessor;

    /**
     * @param class-string<Model> $model
     */
    public function __construct(public string $model)
    {
        $this->relationshipResolverMap = new RelationshipResolverMap(
            new EloquentRelationshipResolver($this),
        );

        $this->relationshipCountResolverMap = new RelationshipCountResolverMap(
            new EloquentRelationshipCountResolver($this),
        );

        $this->relationshipTransformerMap = new RelationshipTransformerMap();
        $this->sortingProcessor = new SortingProcessor($this->relationshipTransformerMap);
        $this->textSearchProcessor = new TextSearchProcessor($this->relationshipTransformerMap);
        $this->filtersProcessor = new FiltersProcessor($this->relationshipTransformerMap);
    }

    public function processCriteria(
        Relation|Builder $query,
        CriteriaContract $criteria,
    ): void {
        if ($attrs = $criteria->getAttributes()) {
            $query->select($attrs);
        }

        if ($relations = $criteria->getRelations()) {
            $this->loadRelations($query, $relations, false);
        }

        if ($counts = $criteria->getRelationCounts()) {
            $this->loadRelationCounts($query, $counts, false);
        }

        if ($textSearch = $criteria->getTextSearch()) {
            $this->textSearchProcessor->processTextSearch($query, $textSearch);
        }

        if ($sorting = $criteria->getSorting()) {
            $this->sortingProcessor->processSorting($query, $sorting);
        }

        if ($filters = $criteria->getFilters()) {
            $this->filtersProcessor->processFiltersCollection($query, $filters);
        }
    }

    /**
     * @param Collection<int,Model> $records
     */
    public function loadMissing(Collection $records, CriteriaContract $criteria): void
    {
        $this->loadMissingAttributes($records, $criteria->getAttributes());
        $this->loadMissingRelations($records, $criteria->getRelations());
        $this->loadMissingRelationCounts($records, $criteria->getRelationCounts());
    }

    /**
     * @param Collection<int,Model> $records
     * @param array<string> $attrs
     */
    protected function loadMissingAttributes(Collection $records, array $attrs): void
    {
        if (!$attrs || $records->isEmpty()) {
            return;
        }

        $first = $records->first();
        $idKey = $first->getKeyName();

        if (!$first->{$idKey}) {
            return;
        }

        $missing = [];
        $loaded = $first->getAttributes();

        foreach ($attrs as $attr) {
            if (!array_key_exists($attr, $loaded)) {
                $missing[] = $attr;
            }
        }

        if (!$missing) {
            return;
        }

        $ids = $records->pluck($idKey)->all();
        $missing[] = $idKey;

        $missingAttrRecords = $this->model::query()
            ->whereIn($idKey, $ids)
            ->get($missing);

        if ($missingAttrRecords->isEmpty()) {
            return;
        }

        $records = $records->groupBy($idKey);
        $record = $missingAttrRecords->first();
        $missingAttrs = $record->getAttributes();

        foreach ($missingAttrRecords as $record) {
            $recToFill = $records[$record->{$idKey}];

            foreach ($missingAttrs as $attr => $value) {
                $recToFill->setAttribute($attr, $value);
            }
        }
    }

    /**
     * @param array<string,?CriteriaContract> $relations
     */
    protected function loadRelations(Relation|Builder $query, array $relations): void
    {
        foreach ($relations as $relation => $criteria) {
            $resolver = $this->relationshipResolverMap->get($relation);
            $resolver->resolveOnQuery($query, $relation, $criteria);
        }
    }

    /**
     * @param Collection<int,Model> $records
     * @param array<string,?CriteriaContract> $relations
     */
    protected function loadMissingRelations(Collection $records, array $relations): void
    {
        foreach ($relations as $relation => $criteria) {
            $resolver = $this->relationshipResolverMap->get($relation);
            $resolver->resolveOnRecords($records, $relation, $criteria);
        }
    }

    /**
     * @param array<string,?CriteriaContract> $counts
     */
    protected function loadRelationCounts(Relation|Builder $query, array $counts): void
    {
        foreach ($counts as $relation => $criteria) {
            $resolver = $this->relationshipCountResolverMap->get($relation);
            $resolver->resolveOnQuery($query, $relation, $criteria);
        }
    }

    /**
     * In contrast to relations loading the count loading is done in bulk.
     * This allows to load counts in a single query when Eloquent resolver is used.
     *
     * @param Collection<int,Model> $records
     * @param array<string,?CriteriaContract> $counts
     */
    public function loadMissingRelationCounts(Collection $records, array $counts): void
    {
        $resolvers = [];
        $resolverCounts = [];

        foreach ($counts as $relation => $criteria) {
            $resolver = $this->relationshipCountResolverMap->get($relation);
            $resolvers[$resolver::class] = $resolver;
            $resolverCounts[$resolver::class][$relation] = $criteria;
        }

        foreach ($resolverCounts as $resolverClass => $counts) {
            $resolvers[$resolverClass]->resolveOnRecords($records, $counts);
        }
    }
}
