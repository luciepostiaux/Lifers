<?php

namespace Database\Factories;

use App\Models\BodyType;
use App\Models\Inventory;
use App\Models\LifeGauge;
use App\Models\Lifer;
use App\Models\LiferGameState;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Lifer> */
class LiferFactory extends Factory
{
    protected $model = Lifer::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'sex' => Lifer::SEX_MALE,
            'born_at' => now(),
            'status' => Lifer::STATUS_ACTIVE,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Lifer $lifer) {
            $body = BodyType::query()->firstOrCreate(
                ['code' => BodyType::CODE_MALE],
                [
                    'label' => 'Corps A',
                    'sex' => Lifer::SEX_MALE,
                    'image_path' => 'images/perso/body-a.png',
                ],
            );

            LiferGameState::query()->firstOrCreate([
                'lifer_id' => $lifer->id,
            ], [
                'body_type_id' => $body->id,
                'money' => 900,
            ]);
            LifeGauge::query()->firstOrCreate(['lifer_id' => $lifer->id]);
            Inventory::query()->firstOrCreate(['lifer_id' => $lifer->id]);
        });
    }
}
