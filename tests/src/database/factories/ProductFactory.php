<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Tests\Database\Factories;

use Deluxetech\LaRepo\Tests\App\Models\Product;
use Deluxetech\LaRepo\Tests\App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory  extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $sku = $this->faker->randomLetter();
        $sku .= $this->faker->randomLetter();
        $sku .= $this->faker->numberBetween(1000, 9999);

        $category = Category::inRandomOrder()->first();

        if (!$category) {
            $category = CategoryFactory::new()->create();
        }

        $createdAt = $this->faker->dateTimeBetween('-3 months', 'now');
        $updatedAt = $this->faker->dateTimeBetween($createdAt, 'now');

        return [
            'category_id' => $category->id,
            'name' => $this->faker->word(),
            'description' => implode(' ', $this->faker->sentences()),
            'price' => $this->faker->randomFloat(2, 100, 1000),
            'sku' => $sku,
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }
}
