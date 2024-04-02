<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SicknessConditions extends Seeder
{
    public function run()
    {
        DB::table('sickness_conditions')->insert([
            // Conditions pour les maladies liées à la négligence
            [
                'sickness_id' => 6,
                'condition_type' => 'absence_sport',
                'threshold' => 10, // Jours sans sport
            ],
            [
                'sickness_id' => 7,
                'condition_type' => 'absence_sport',
                'threshold' => 15, // Jours sans sport
            ],
            [
                'sickness_id' => 8,
                'condition_type' => 'poor_dental_hygiene',
                'threshold' => 6, // Jours sans utilisation de dentifrice
            ],
            [
                'sickness_id' => 9,
                'condition_type' => 'low_happiness',
                'threshold' => 6, // Jours avec un niveau de bonheur très bas
            ],
            
            // maladies grave

            [
                'sickness_id' => 10, 
                'condition_type' => 'age_range',
                'threshold' => 50, // Suppose que la surveillance commence à 50 ans
            ],
            [
                'sickness_id' => 11, 
                'condition_type' => 'age',
                'threshold' => 75, // Un taux d'apparition plus élevé chez les personnes de plus de 75 ans
            ],
            [
                'sickness_id' => 12, 
                'condition_type' => 'low_gauges',
                'threshold' => 0, // Le seuil peut être zéro car cela nécessite une logique complexe: la santé et la condition physique doivent être bas
            ],
        ]);
    }
}
