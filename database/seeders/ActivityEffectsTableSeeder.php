<?php

namespace Database\Seeders;

use App\Models\ActivityEffect;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActivityEffectsTableSeeder extends Seeder
{
    public function run()
    {
        // Vous devrez remplacer les 'activity_id' par les ID réels correspondants à chaque activité.
        $activityEffects = [
            // Effets pour le Spa
            ['activity_id' => 1, 'effect_type' => 'happiness', 'effect_value' => 20],
            ['activity_id' => 2, 'effect_type' => 'happiness', 'effect_value' => 35],
            ['activity_id' => 3, 'effect_type' => 'happiness', 'effect_value' => 55],

            // Effets pour le Parc d'attractions
            ['activity_id' => 4, 'effect_type' => 'entertainment', 'effect_value' => 20],
            ['activity_id' => 4, 'effect_type' => 'happiness', 'effect_value' => 10],
            ['activity_id' => 5, 'effect_type' => 'entertainment', 'effect_value' => 35],
            ['activity_id' => 5, 'effect_type' => 'happiness', 'effect_value' => 15],
            ['activity_id' => 6, 'effect_type' => 'entertainment', 'effect_value' => 50],
            ['activity_id' => 6, 'effect_type' => 'happiness', 'effect_value' => 25],

            // Effets pour le Zoo
            ['activity_id' => 7, 'effect_type' => 'entertainment', 'effect_value' => 15],
            ['activity_id' => 7, 'effect_type' => 'happiness', 'effect_value' => 25],
            ['activity_id' => 8, 'effect_type' => 'entertainment', 'effect_value' => 25],
            ['activity_id' => 8, 'effect_type' => 'happiness', 'effect_value' => 40],
            ['activity_id' => 9, 'effect_type' => 'entertainment', 'effect_value' => 55],
            ['activity_id' => 9, 'effect_type' => 'happiness', 'effect_value' => 40],

            // Effets pour le Cinéma
            ['activity_id' => 10, 'effect_type' => 'entertainment', 'effect_value' => 15],
            ['activity_id' => 10, 'effect_type' => 'happiness', 'effect_value' => 5],
            ['activity_id' => 11, 'effect_type' => 'entertainment', 'effect_value' => 35],
            ['activity_id' => 11, 'effect_type' => 'happiness', 'effect_value' => 30],
            ['activity_id' => 12, 'effect_type' => 'entertainment', 'effect_value' => 45],
            ['activity_id' => 12, 'effect_type' => 'happiness', 'effect_value' => 40],
        ];

        foreach ($activityEffects as $effect) {
            ActivityEffect::create($effect);
        }
    }
}
