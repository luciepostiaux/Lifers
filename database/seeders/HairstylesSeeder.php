<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Hairstyle;

class HairstylesSeeder extends Seeder
{
    public function run()
    {
        Hairstyle::insert([
            ['name' => 'Coupe courte', 'description' => 'Description de la coupe courte', 'price' => 10.99],
            ['name' => 'Coupe mi-longue', 'description' => 'Description de la coupe mi-longue', 'price' => 14.99],
            ['name' => 'Coupe longue', 'description' => 'Description de la coupe longue', 'price' => 19.99],
        ]);
    }
}
