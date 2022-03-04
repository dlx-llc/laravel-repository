<?php

namespace LaravelRepository\Tests\Feature;

use LaravelRepository\Contracts\DataAttrContract;
use LaravelRepository\FilterFactory;
use LaravelRepository\Tests\TestCase;
use LaravelRepository\FilterOptimizer;
use LaravelRepository\Enums\FilterMode;
use LaravelRepository\Enums\FilterOperator;
use LaravelRepository\Contracts\FiltersCollectionContract;

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
            FilterMode::IS_LIKE,
            'attr1',
            'val1',
            FilterOperator::OR
        );

        $filter2 = FilterFactory::create(
            FilterMode::IS_LIKE,
            'attr2',
            'val2',
            FilterOperator::AND
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
