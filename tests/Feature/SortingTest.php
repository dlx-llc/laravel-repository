<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Tests\Feature;

use PHPUnit\Framework\Attributes\Group;
use Deluxetech\LaRepo\Tests\App\Http\Controllers\ProductController;

#[Group('feature')]
final class SortingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->generateFakeData();
    }

    public function testSortingDesc(): void
    {
        /** @var ProductController $controller */
        $controller = $this->app->make(ProductController::class);
        request()->replace([
            'page' => 1,
            'perPage' => 10,
            'sort' => 'id,desc',
        ]);

        $result = $controller->index();

        for ($i = 0, $j = 1; $j < $result->count(); $i++, $j++) {
            $this->assertLessThan($result[$i]->id, $result[$j]->id);
        }
    }

    public function testSortingWithDateColumnAsc(): void
    {
        /** @var ProductController $controller */
        $controller = $this->app->make(ProductController::class);
        request()->replace([
            'page' => 1,
            'perPage' => 10,
            'sort' => 'createdAt,asc',
        ]);

        $result = $controller->index();

        for ($i = 0, $j = 1; $j < $result->count(); $i++, $j++) {
            $this->assertLessThanOrEqual(
                $result[$j]->created_at->timestamp,
                $result[$i]->created_at->timestamp,
            );
        }
    }
}
