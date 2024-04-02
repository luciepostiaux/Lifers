<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Perso>
 */
class PersoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'birth_date' => $this->faker->date,
            'money' => $this->faker->numberBetween(1000, 100000),
            'user_id' => \App\Models\User::inRandomOrder()->first()->id,
            'hairstyles_id' => $this->faker->randomElement([1, null]), // Ajustez selon vos IDs existants
            'perso_outfits_id' => null,
            'jobs_id' => $this->faker->randomElement([1, null]), // Idem
            'salary' => null,
            'mother_id' => null,
            'father_id' => null,
            'partner_id' => null,
            'perso_bodies_id' => $this->faker->randomElement([1, 2]), // Idem        
        ];
    }
}
