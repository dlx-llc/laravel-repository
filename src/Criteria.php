<?php

namespace Deluxetech\LaRepo;

use Deluxetech\LaRepo\Contracts\CriteriaContract;

class Criteria implements CriteriaContract
{
    use Traits\SupportsSorting;
    use Traits\SupportsTextSearch;
    use Traits\SupportsFiltration;
    use Traits\DefinesQueryContext;

    /** @inheritdoc */
    public function merge(CriteriaContract $criteria): static
    {
        if ($attributes = $criteria->getAttributes()) {
            $attributes = array_unique([...$this->getAttributes(), ...$attributes]);
            $this->setAttributes(...$attributes);
        }

        if ($relations = $criteria->getRelations()) {
            foreach ($relations as $relation => $relCriteria) {
                $this->addRelation($relation, $relCriteria);
            }
        }

        if ($counts = $criteria->getRelationCounts()) {
            foreach ($counts as $relation => $relCriteria) {
                $this->addRelationCount($relation, $relCriteria);
            }
        }

        if ($sorting = $criteria->getSorting()) {
            $this->setSorting($sorting);
        }

        if ($textSearch = $criteria->getTextSearch()) {
            $this->setTextSearch($textSearch);
        }

        if ($filters = $criteria->getFilters()) {
            if (is_null($this->filters)) {
                $this->filters = $filters;
            } else {
                $this->filters->add($filters);
            }
        }

        return $this;
    }
}
