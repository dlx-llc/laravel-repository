<?php

namespace Deluxetech\LaRepo\Tests\Feature;

use Deluxetech\LaRepo\FilterFactory;
use Deluxetech\LaRepo\Tests\TestCase;
use Deluxetech\LaRepo\FilterOptimizer;
use Deluxetech\LaRepo\Enums\FilterOperator;
use Deluxetech\LaRepo\Enums\BooleanOperator;
use Deluxetech\LaRepo\Contracts\FiltersCollectionContract;

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
        $collection = $this->createFiltersCollection();
        $nestedIdleCollection = $this->createFiltersCollection();

        $filter1 = FilterFactory::create(
            FilterOperator::IS_LIKE,
            'attr1',
            'val1',
            BooleanOperator::OR
        );

        $filter2 = FilterFactory::create(
            FilterOperator::IS_LIKE,
            'attr2',
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

    /**
     * Returns new filter collection instance.
     *
     * @return FiltersCollectionContract
     */
    protected function createFiltersCollection(): FiltersCollectionContract
    {
        return $this->app->make(FiltersCollectionContract::class);
    }
}
