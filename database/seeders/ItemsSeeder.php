<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;

class ItemsSeeder extends Seeder
{
    public function run()
    {
        Item::create([
            'name' => 'Pomme',
            'price' => 1.99,
            'description' => 'Une belle pomme rouge remplie de bienfaits.',
            'img_item' => 'images/items/foods/apple.png',
            'category' => 'Nourriture',
        ]);

        Item::factory()->count(10)->create();
    }
}
