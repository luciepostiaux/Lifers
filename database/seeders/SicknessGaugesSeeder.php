<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SicknessGauge;

class SicknessGaugesSeeder extends Seeder
{
    public function run()
    {
        SicknessGauge::insert([
            ['name' => 'Fatigue'],
            ['name' => 'Douleur'],
            ['name' => 'Faim accrue'],
        ]);
    }
}
