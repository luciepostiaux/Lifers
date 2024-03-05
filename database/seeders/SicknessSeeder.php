<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sickness;

class SicknessSeeder extends Seeder
{
    public function run()
    {
        Sickness::insert([
            ['name' => 'Grippe', 'description' => 'Infection virale courante', 'duration' => 3, 'chance' => 30],
            ['name' => 'Varicelle', 'description' => 'Maladie contagieuse caractérisée par de petites vésicules', 'duration' => 6, 'chance' => 10],
        ]);
    }
}
