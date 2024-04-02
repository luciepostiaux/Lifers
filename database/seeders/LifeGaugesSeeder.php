<?php

namespace Database\Seeders;

use App\Models\LifeGauge;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LifeGaugesSeeder extends Seeder
{
    public function run(): void
    {
        $persoId = 1;
        LifeGauge::create([
            'perso_id' => $persoId,
            'hunger' => 100,
            'thirst' => 100,
            'clean' => 100,
            'happiness' => 100,
            'entertainment' => 100,
            'physical_condition' => 100,
            'health' => 100,
        ]);
        $persoId = 2;
        LifeGauge::create([
            'perso_id' => $persoId,
            'hunger' => 100,
            'thirst' => 100,
            'clean' => 100,
            'happiness' => 100,
            'entertainment' => 100,
            'physical_condition' => 100,
            'health' => 100,
        ]);
    }
}
