<?php

namespace Database\Factories;

use App\Models\OutfitTop;
use Illuminate\Database\Eloquent\Factories\Factory;

class OutfitTopFactory extends Factory
{
    protected $model = OutfitTop::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 10, 500), 
        ];
    }
}
