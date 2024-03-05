<?php

namespace Database\Factories;

use App\Models\OutfitShoe;
use Illuminate\Database\Eloquent\Factories\Factory;

class OutfitShoeFactory extends Factory
{
    protected $model = OutfitShoe::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 10, 500),
        ];
    }
}
