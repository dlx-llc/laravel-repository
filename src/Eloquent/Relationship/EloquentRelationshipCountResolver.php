<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Eloquent\Relationship;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Deluxetech\LaRepo\Contracts\CriteriaContract;
use Illuminate\Database\Eloquent\Relations\Relation;
use Deluxetech\LaRepo\Eloquent\Criteria\CriteriaProcessor;

class EloquentRelationshipCountResolver implements RelationshipCountResolverContract
{
    public function __construct(public CriteriaProcessor $criteriaProcessor)
    {
    }

    public function resolveOnRecords(Collection $records, array $counts): void
    {
        $missing = [];
        $first = $records->first();

        foreach ($counts as $relation => $criteria) {
            $countAttr = $relation . 'Count';

            if (isset($first->{$countAttr})) {
                continue;
            }

            $countExpression = "{$relation} as {$countAttr}";

            if ($criteria) {
                $missing[$countExpression] = fn (Relation|Builder $query) => $this->criteriaProcessor
                    ->processCriteria($query, $criteria);
            } else {
                $missing[] = $countExpression;
            }
        }

        if ($missing) {
            $records->loadCount($missing);
        }
    }

    public function resolveOnQuery(Relation|Builder $query, string $relation, ?CriteriaContract $criteria): void
    {
        $countExpression = "{$relation} as {$relation}Count";

        if ($criteria) {
            $query->withCount([
                $countExpression => fn (Relation|Builder $subQuery) => $this->criteriaProcessor->processCriteria(
                    $subQuery,
                    $criteria,
                ),
            ]);
        } else {
            $query->withCount($countExpression);
        }
    }
}
