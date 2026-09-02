<?php

namespace Database\Factories;

use App\Models\Lifer;
use App\Models\Suggestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Suggestion> */
class SuggestionFactory extends Factory
{
    protected $model = Suggestion::class;

    public function definition(): array
    {
        return [
            'lifer_id' => Lifer::factory(),
            'content' => fake()->text(),
            'status' => 'pending',
        ];
    }
}
