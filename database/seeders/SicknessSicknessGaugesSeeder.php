<?php

namespace Database\Seeders;

use App\Models\Sickness;
use App\Models\SicknessGauge;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SicknessSicknessGaugesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Trouver les maladies et les jauges par leur nom ou un autre attribut unique
        $sickness = Sickness::where('id', '1')->first();
        $sicknessGauge = SicknessGauge::where('id', '1')->first();

        // Vérifier que les entités existent
        if ($sickness && $sicknessGauge) {
            // Associer la maladie à la jauge avec une valeur d'effet
            $sickness->gauges()->attach($sicknessGauge->id, ['effect_value' => 20]);
        }
    }
}
