<?php

namespace Deluxetech\LaRepo\Tests\Unit\FilterOptimizer;

use Deluxetech\LaRepo\Tests\TestCase;
use Deluxetech\LaRepo\FilterOptimizer;
use Deluxetech\LaRepo\Enums\BooleanOperator;
use Deluxetech\LaRepo\Contracts\FilterContract;
use Deluxetech\LaRepo\Contracts\FiltersCollectionContract;
use Deluxetech\LaRepo\Tests\Unit\Traits\CallsPrivateMethods;

/**
 * @group unit
 * @group DecomposeIdleCollectionTest
 * @see FilterOptimizer::decomposeIdleCollection()
 */
final class DecomposeIdleCollectionTest extends TestCase
{
    use CallsPrivateMethods;

    /**
     * @var string
     */
    private const TESTEE = 'decomposeIdleCollection';

    /**
     * Test method returns empty array when empty collection is given.
     *
     * @return void
     */
    public function testReturnsEmptyArrayWhenEmptyCollectionGiven(): void
    {
        $optimizer = new FilterOptimizer();

        $collection = $this->createMock(FiltersCollectionContract::class);
        $collection->expects($this->once())->method('isEmpty')->willReturn(true);

        $result = $this->callPrivateMethod($optimizer, self::TESTEE, [$collection]);

        $this->assertEquals([], $result);
    }

    /**
     * Test method returns an array containing the only item in the collection.
     *
     * @return void
     */
    public function testArrayOfTheOnlyItemReturned(): void
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

        $this->assertEquals([$item], $result);
    }

    /**
     * Test method returns an array of collection items when it contains only filters
     * that have AND logical operators.
     *
     * @return void
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

        $collection->expects($this->atLeastOnce())->method('isEmpty')->willReturn(false);
        $collection->expects($this->atLeastOnce())->method('count')->willReturn(count($items));

        $offsetGetRetValMap = [];

        foreach ($items as $i => $item) {
            $offsetGetRetValMap[] = [$i, $item];
            $item->expects($this->any())->method('getBoolean')->willReturn(BooleanOperator::AND);
        }

        $collection->expects($this->atLeastOnce())
            ->method('offsetGet')
            ->will($this->returnValueMap($offsetGetRetValMap));

        $collection->expects($this->atLeastOnce())->method('getBoolean')->willReturn($collectionOperator);
        $items[0]->expects($this->atLeastOnce())->method('setBoolean')->with($collectionOperator);
        $collection->expects($this->atLeastOnce())->method('getItems')->willReturn($items);

        $result = $this->callPrivateMethod($optimizer, self::TESTEE, [$collection]);

        $this->assertEquals($items, $result);
    }
}
