<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Tests\Feature;

use Deluxetech\LaRepo\Facades\LaRepo;
use Deluxetech\LaRepo\Tests\TestCase;
use Deluxetech\LaRepo\FilterOptimizer;
use PHPUnit\Framework\Attributes\Group;
use Deluxetech\LaRepo\Enums\FilterOperator;
use Deluxetech\LaRepo\Enums\BooleanOperator;

#[Group('feature')]
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

        $filter3Sub = LaRepo::newFilter(
            'attr3',
            FilterOperator::EQUALS_TO,
            'testing',
            BooleanOperator::OR
        );

        $filter3 = LaRepo::newFilter(
            'rel1',
            FilterOperator::EXISTS,
            LaRepo::newFiltersCollection(BooleanOperator::OR, $filter3Sub),
            BooleanOperator::AND
        );

        $nestedIdleCollection->setItems($filter1, $filter2, $filter3);
        $collection->setItems($nestedIdleCollection);

        $optimizer->optimize($collection);

        $this->assertCount(3, $collection);
        $this->assertEquals($filter1, $collection[0]);
        $this->assertEquals($filter2, $collection[1]);
        $this->assertEquals($filter3Sub, $collection[2]);
        $this->assertEquals('rel1.attr3', $collection[2]->getAttr()->getName());
        $this->assertEquals(BooleanOperator::AND, $collection[2]->getBoolean());
    }
}
