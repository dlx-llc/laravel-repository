<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Tests\Database\Factories;

use Deluxetech\LaRepo\Tests\App\Models\Label;
use Illuminate\Database\Eloquent\Factories\Factory;

class LabelFactory  extends Factory
{
    protected $model = Label::class;

    public function definition(): array
    {
        $createdAt = $this->faker->dateTimeBetween('-3 months', 'now');
        $updatedAt = $this->faker->dateTimeBetween($createdAt, 'now');

        return [
            'name' => $this->faker->colorName(),
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }
}
