<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Tests\Feature;

use PHPUnit\Framework\Attributes\Group;
use Deluxetech\LaRepo\Tests\Database\Factories\LabelFactory;
use Deluxetech\LaRepo\Tests\Database\Factories\ProductFactory;
use Deluxetech\LaRepo\Tests\Database\Factories\CategoryFactory;
use Deluxetech\LaRepo\Tests\App\Http\Controllers\ProductController;

#[Group('feature')]
final class TextSearchTest extends TestCase
{
    public function testTextSearch(): void
    {
        $labelFactory = LabelFactory::new();
        $categoryFactory = CategoryFactory::new();
        $productFactory = ProductFactory::new();

        $regularSedanLabel = $labelFactory->create(['name' => 'Regular Sedan']);
        $premiumSedanLabel = $labelFactory->create(['name' => 'Premium Sedan']);
        $premiumFoldableLabel = $labelFactory->create(['name' => 'Premium Foldable']);
        $nonPremiumSilverPlatedLabel = $labelFactory->create(['name' => 'Non-premium Silver-plated']);

        $carsCategory = $categoryFactory->create(['name' => 'Cars']);
        $phonesCategory = $categoryFactory->create(['name' => 'Phones']);
        $watchesCategory = $categoryFactory->create(['name' => 'Watches']);
        $premiumWatchesCategory = $categoryFactory->create(['name' => 'Premium Watches']);

        // below records should not match
        $toyotaCorolla = $productFactory->create(['name' => 'Toyota Corolla', 'category_id' => $carsCategory->id]);
        $toyotaCorolla->labels()->attach($regularSedanLabel);
        $hyundaiElantra = $productFactory->create(['name' => 'Hyundai Elantra', 'category_id' => $carsCategory->id]);
        $hyundaiElantra->labels()->attach($regularSedanLabel);
        $productFactory->create(['name' => 'iPhone 16', 'category_id' => $phonesCategory->id]);

        // below records should match by a label name
        $bmw7Series = $productFactory->create(['name' => 'BMW 7 Series', 'category_id' => $carsCategory->id]);
        $bmw7Series->labels()->attach($premiumSedanLabel);
        $samsungGalaxyFold = $productFactory->create(['name' => 'Samsung Galaxy Fold', 'category_id' => $phonesCategory->id]);
        $samsungGalaxyFold->labels()->attach($premiumFoldableLabel);
        $casio = $productFactory->create(['name' => 'Casio', 'category_id' => $watchesCategory->id]);
        $casio->labels()->attach($nonPremiumSilverPlatedLabel);

        // below record should match by the product name
        $premiumCarS10 = $productFactory->create(['name' => 'PremiumCar S10', 'category_id' => $carsCategory->id]);
        $premiumCarS10->labels()->attach($regularSedanLabel);

        // below records should match by the category name
        $rolexA1 = $productFactory->create(['name' => 'Rolex A1', 'category_id' => $premiumWatchesCategory->id]);
        $rolexA2 = $productFactory->create(['name' => 'Rolex A2', 'category_id' => $premiumWatchesCategory->id]);

        /** @var ProductController $controller */
        $controller = $this->app->make(ProductController::class);
        request()->replace([
            'page' => 1,
            'perPage' => 10,
            'search' => 'premium,[name,category.name,labels.name]',
        ]);

        $result = $controller->index();
        $resultIds = $result->pluck('id')->all();
        $assumablyEmptySet = $result->whereIn('name', [
            'Toyota Corolla',
            'Hyundai Elantra',
            'iPhone 16',
        ]);

        $this->assertEquals(6, $result->count());
        $this->assertEmpty($assumablyEmptySet);
        $this->assertContains($bmw7Series->id, $resultIds);
        $this->assertContains($premiumCarS10->id, $resultIds);
        $this->assertContains($samsungGalaxyFold->id, $resultIds);
        $this->assertContains($rolexA1->id, $resultIds);
        $this->assertContains($rolexA2->id, $resultIds);
        $this->assertContains($casio->id, $resultIds);
    }
}
