<?php

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    /**
     * Le nom du modèle d'usine correspondant.
     *
     * @var string
     */
    protected $model = Item::class;

    /**
     * Définissez l'état par défaut du modèle.
     *
     * @return array
     */
    public function definition()
    {
        // return [
        //     'name' => $this->faker->word,
        //     'price' => $this->faker->randomFloat(2, 0.99, 100.00),
        //     'description' => $this->faker->sentence,
        //     'img_item' => 'images/items/foods/' . $this->faker->numberBetween(1, 100) . '.jpg',
        //     'category' => $this->faker->randomElement(['Nourriture', 'Boisson', 'Décoration']),
        // ];
    }
}
