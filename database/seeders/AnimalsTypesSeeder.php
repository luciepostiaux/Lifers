<?php

namespace Database\Seeders;

use App\Models\AnimalsType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AnimalsTypesSeeder extends Seeder
{
    public function run(): void
    {
        AnimalsType::insert([
            ['name' => 'Chat', 'description' => 'Petit carnivore domestique'],
            ['name' => 'Chien', 'description' => 'Meilleur ami de l\'homme'],
        ]);
    }
}
