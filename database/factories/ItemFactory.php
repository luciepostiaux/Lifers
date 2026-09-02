<?php

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Item> */
class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'price' => fake()->randomFloat(2, 1, 100),
            'description' => fake()->sentence(),
            'image_path' => null,
            'background_image_path' => null,
            'category' => fake()->randomElement(['food', 'drink', 'care']),
        ];
    }
}
