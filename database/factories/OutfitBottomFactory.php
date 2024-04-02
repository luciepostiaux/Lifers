<?php

namespace Database\Factories;

use App\Models\OutfitBottom;
use Illuminate\Database\Eloquent\Factories\Factory;

class OutfitBottomFactory extends Factory
{
    protected $model = OutfitBottom::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 10, 500),
        ];
    }
}
