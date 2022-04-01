<?php

namespace Deluxetech\LaRepo\Tests\Feature;

use Deluxetech\LaRepo\Facades\LaRepo;
use Deluxetech\LaRepo\Tests\TestCase;
use Deluxetech\LaRepo\FilterOptimizer;
use Deluxetech\LaRepo\Enums\FilterOperator;
use Deluxetech\LaRepo\Enums\BooleanOperator;

/**
 * @group feature
 * @group FilterOptimizerFeature
 */
final class FilterOptimizerTest extends TestCase
{
    /**
     * Test optimizer works correctly.
     *
     * @return void
     */
    public function testFilterOptimizer(): void
    {
        $optimizer = new FilterOptimizer();
        $collection = LaRepo::newFiltersCollection();
        $nestedIdleCollection = LaRepo::newFiltersCollection();

        $filter1 = LaRepo::newFilter(
            'attr1',
            FilterOperator::IS_LIKE,
            'val1',
            BooleanOperator::OR
        );

        $filter2 = LaRepo::newFilter(
            'attr2',
            FilterOperator::IS_LIKE,
            'val2',
            BooleanOperator::AND
        );

        $nestedIdleCollection->setItems($filter1, $filter2);
        $collection->setItems($nestedIdleCollection);

        $optimizer->optimize($collection);

        $this->assertCount(2, $collection);
        $this->assertEquals($filter1, $collection[0]);
        $this->assertEquals($filter2, $collection[1]);
    }
}
