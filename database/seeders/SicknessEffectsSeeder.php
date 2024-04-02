<?php

namespace Database\Seeders;

use App\Models\LifeGauge;
use App\Models\Sickness;
use App\Models\SicknessGauge;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SicknessEffectsSeeder extends Seeder
{
    public function run()
    {
        DB::table('sickness_effects')->insert([
            // Effets de la Gastro-entérite
            ['sickness_id' => 1, 'gauge' => 'clean', 'effect' => -15],
            ['sickness_id' => 1, 'gauge' => 'thirst', 'effect' => -20],
            ['sickness_id' => 1, 'gauge' => 'happiness', 'effect' => -10],

            // Effets du Rhume
            ['sickness_id' => 2, 'gauge' => 'health', 'effect' => -20],
            ['sickness_id' => 2, 'gauge' => 'clean', 'effect' => -10],

            // Effets des Maux de tête
            ['sickness_id' => 3, 'gauge' => 'happiness', 'effect' => -20],
            ['sickness_id' => 3, 'gauge' => 'health', 'effect' => -15],

            // Effets de l'Allergie saisonnière
            ['sickness_id' => 4, 'gauge' => 'health', 'effect' => -15],
            ['sickness_id' => 4, 'gauge' => 'happiness', 'effect' => -15],

            // Effets de l'Intoxication alimentaire
            ['sickness_id' => 5, 'gauge' => 'health', 'effect' => -30],
            ['sickness_id' => 5, 'gauge' => 'clean', 'effect' => -20],

            // Diabète de type 2
            ['sickness_id' => 6, 'gauge' => 'health', 'effect' => -10],
            ['sickness_id' => 6, 'gauge' => 'physical_condition', 'effect' => -20],

            // Obésité
            ['sickness_id' => 7, 'gauge' => 'health', 'effect' => -20],
            ['sickness_id' => 7, 'gauge' => 'physical_condition', 'effect' => -25],

            // Caries dentaires
            ['sickness_id' => 8, 'gauge' => 'happiness', 'effect' => -10],
            ['sickness_id' => 8, 'gauge' => 'health', 'effect' => -20],

            // Dépression
            ['sickness_id' => 9, 'gauge' => 'happiness', 'effect' => -30],
            ['sickness_id' => 9, 'gauge' => 'entertainment', 'effect' => -20],

            // Cancer
            ['sickness_id' => 10, 'gauge' => 'health', 'effect' => -40],

            // Maladie incurable
            ['sickness_id' => 11, 'gauge' => 'health', 'effect' => -100], // Exemple extrême

            // Insuffisance rénale
            ['sickness_id' => 12, 'gauge' => 'health', 'effect' => -35],
            ['sickness_id' => 12, 'gauge' => 'physical_condition', 'effect' => -15],
        ]);
    }
}
