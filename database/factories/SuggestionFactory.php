<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Suggestion>
 */
class SuggestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::exists() ? User::inRandomOrder()->first()->id : User::factory(),
            'content' => $this->faker->text,
            'status' => $this->faker->randomElement(['Ouvert', 'Fermé', 'En cours']),
        ];
    }
}
