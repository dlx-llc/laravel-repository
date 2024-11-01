<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Tests\Feature;

use PHPUnit\Framework\Attributes\Group;
use Illuminate\Validation\ValidationException;
use Deluxetech\LaRepo\Tests\App\Models\Product;
use Deluxetech\LaRepo\Tests\App\Http\Controllers\ProductController;

#[Group('feature')]
final class PaginationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->generateFakeData();
    }

    public function testPaginationRequired(): void
    {
        /** @var ProductController $controller */
        $controller = $this->app->make(ProductController::class);
        request()->replace(['page' => null]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageMatches('/^The page field must be an integer\..*/');

        $controller->index();
    }

    public function testPaginatedAsExpected(): void
    {
        /** @var ProductController $controller */
        $controller = $this->app->make(ProductController::class);
        request()->replace([
            'page' => 2,
            'perPage' => 10,
        ]);

        $result = $controller->index();
        $totalNumber = Product::query()->count();

        $this->assertEquals(10, $result->count());
        $this->assertEquals(2, $result->currentPage());
        $this->assertEquals($totalNumber, $result->total());
    }
}
