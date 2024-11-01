<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Tests\Database\Factories;

use Deluxetech\LaRepo\Tests\App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory  extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $createdAt = $this->faker->dateTimeBetween('-3 months', 'now');
        $updatedAt = $this->faker->dateTimeBetween($createdAt, 'now');

        return [
            'name' => $this->faker->word(),
            'description' => implode(' ', $this->faker->sentences()),
            'is_active' => $this->faker->boolean(),
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }
}
