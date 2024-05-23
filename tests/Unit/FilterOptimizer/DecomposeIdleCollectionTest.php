<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Tests\Unit\FilterOptimizer;

use Deluxetech\LaRepo\Tests\TestCase;
use Deluxetech\LaRepo\FilterOptimizer;
use PHPUnit\Framework\Attributes\Group;
use Deluxetech\LaRepo\Enums\BooleanOperator;
use Deluxetech\LaRepo\Contracts\FilterContract;
use Deluxetech\LaRepo\Contracts\FiltersCollectionContract;
use Deluxetech\LaRepo\Tests\Unit\Traits\CallsPrivateMethods;

#[Group('unit')]
final class DecomposeIdleCollectionTest extends TestCase
{
    use CallsPrivateMethods;

    private const TESTEE = 'decomposeIdleCollection';

    /**
     * Test method returns null when empty collection is given.
     */
    public function testReturnsEmptyArrayWhenEmptyCollectionGiven(): void
    {
        $optimizer = new FilterOptimizer();

        $collection = $this->createMock(FiltersCollectionContract::class);
        $collection->expects($this->once())->method('isEmpty')->willReturn(true);

        $result = $this->callPrivateMethod($optimizer, self::TESTEE, [$collection]);

        $this->assertNull($result);
    }

    /**
     * Test method returns the only item in the collection.
     */
    public function testTheOnlyItemReturned(): void
    {
        $optimizer = new FilterOptimizer();
        $collectionOperator = BooleanOperator::OR;

        $item = $this->createMock(FilterContract::class);
        $collection = $this->createMock(FiltersCollectionContract::class);

        $collection->expects($this->once())->method('isEmpty')->willReturn(false);
        $collection->expects($this->once())->method('count')->willReturn(1);
        $collection->expects($this->once())->method('offsetGet')->with(0)->willReturn($item);
        $collection->expects($this->once())->method('getBoolean')->willReturn($collectionOperator);
        $item->expects($this->once())->method('setBoolean')->with($collectionOperator);

        $result = $this->callPrivateMethod($optimizer, self::TESTEE, [$collection]);

        $this->assertEquals($item, $result);
    }

    /**
     * Test method returns an array of collection items when it contains only filters
     * that have AND logical operators.
     */
    public function testAndLogicalOperatorCollectionDecomposed(): void
    {
        $optimizer = new FilterOptimizer();
        $collectionOperator = BooleanOperator::OR;

        $collection = $this->createMock(FiltersCollectionContract::class);
        $items = [
            $this->createMock(FilterContract::class),
            $this->createMock(FilterContract::class),
        ];

        $collection->expects($this->once())->method('isEmpty')->willReturn(false);
        $collection->expects($this->once())->method('count')->willReturn(count($items));
        $collection->expects($this->once())->method('containsBoolOr')->willReturn(false);
        $collection->expects($this->once())->method('offsetGet')->with(0)->willReturn($items[0]);
        $collection->expects($this->once())->method('getBoolean')->willReturn($collectionOperator);
        $items[0]->expects($this->once())->method('setBoolean')->with($collectionOperator);
        $collection->expects($this->atLeastOnce())->method('getItems')->willReturn($items);

        $result = $this->callPrivateMethod($optimizer, self::TESTEE, [$collection]);

        $this->assertEquals($items, $result);
    }
}
