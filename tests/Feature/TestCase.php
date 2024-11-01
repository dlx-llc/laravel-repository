<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Tests\Feature;

use Deluxetech\LaRepo\Tests\TestCase as BaseTestCase;
use Deluxetech\LaRepo\Tests\Database\Factories\LabelFactory;
use Deluxetech\LaRepo\Tests\Database\Factories\ProductFactory;
use Deluxetech\LaRepo\Tests\Database\Factories\CategoryFactory;
use Deluxetech\LaRepo\Tests\App\Providers\TestAppServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    protected function tearDown(): void
    {
        $this->artisan('migrate:refresh');
        parent::tearDown();
    }

    protected function getPackageProviders($app): array
    {
        return [
            ...parent::getPackageProviders($app),
            TestAppServiceProvider::class,
        ];
    }

    protected function generateFakeData(
        int $labelCount = 50,
        int $categoryCount = 3,
        int $productPerCategoryCount = 50,
        int $labelPerProductCount = 10,
    ): void {
        $labels = LabelFactory::new()->count($labelCount)->create();
        $categories = CategoryFactory::new()->count($categoryCount)->create();

        foreach ($categories as $category) {
            $products = ProductFactory::new()
                ->count($productPerCategoryCount)
                ->create(['category_id' => $category->id]);

            foreach ($products as $product) {
                $product->labels()->attach($labels->random($labelPerProductCount));
            }
        }
    }
}
