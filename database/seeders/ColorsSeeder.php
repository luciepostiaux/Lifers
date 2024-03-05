<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Color;

class ColorsSeeder extends Seeder
{
    public function run()
    {
        Color::insert([
            ['name' => 'Rouge', 'description' => 'La couleur de la passion', 'hexa_code' => '#FF423D'],
            ['name' => 'Vert', 'description' => 'La couleur de la nature', 'hexa_code' => '#74E887'],
            ['name' => 'Bleu', 'description' => 'La couleur du ciel', 'hexa_code' => '#7CC9FF'],
        ]);
    }
}
