<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sickness;
use Illuminate\Support\Facades\DB;

class SicknessSeeder extends Seeder
{
    public function run()
    {
        DB::table('sickness')->insert([
            // Maladies aléatoires
            [
                'name' => 'Gastro-entérite',
                'description' => 'Réduit légèrement la propreté, la soif et le bonheur du personnage pendant une journée.',
                'duration' => 1,
                'chance' => json_encode([
                    '18-34' => 5,
                    '35-59' => 7,
                    '60-79' => 10,
                    '80+' => 15,
                ]),
                'type' => 'random',
                'needs_doctor' => false,
                'self_resolving' => true,
                'treatment_cost' => 90.00,
            ],
            [
                'name' => 'Rhume',
                'description' => 'Diminue temporairement la santé et la propreté du personnage pendant quelques jours.',
                'duration' => 1,
                'chance' => json_encode([
                    '18-34' => 5,
                    '35-59' => 7,
                    '60-79' => 10,
                    '80+' => 15,
                ]),
                'type' => 'random',
                'needs_doctor' => false,
                'self_resolving' => true,
                'treatment_cost' => 120.00,
            ], [
                'name' => 'Maux de tête',
                'description' => ' Diminue le bonheur et la santé du personnage pendant une période limitée.',
                'duration' => 1,
                'chance' => json_encode([
                    '18-34' => 5,
                    '35-59' => 7,
                    '60-79' => 10,
                    '80+' => 15,
                ]),
                'type' => 'random',
                'needs_doctor' => false,
                'self_resolving' => true,
                'treatment_cost' => 800.00,
            ],
            [
                'name' => 'Allergie saisonnière',
                'description' => 'Augmente la fatigue et diminue la santé du personnage pendant la saison concernée.',
                'duration' => 1,
                'chance' => json_encode([
                    '18-34' => 5,
                    '35-59' => 7,
                    '60-79' => 10,
                    '80+' => 15,
                ]),
                'type' => 'random',
                'needs_doctor' => false,
                'self_resolving' => true,
                'treatment_cost' => 180.00,
            ], [
                'name' => 'Intoxication alimentaire',
                'description' => 'Réduit temporairement la santé et la propreté du personnage en raison d\'une alimentation contaminée.',
                'duration' => 1,
                'chance' => json_encode([
                    '18-34' => 5,
                    '35-59' => 7,
                    '60-79' => 10,
                    '80+' => 15,
                ]),
                'type' => 'random',
                'needs_doctor' => false,
                'self_resolving' => true,
                'treatment_cost' => 100.00,
            ],
            // Maladies liées à la négligence des jauges
            [
                'name' => 'Diabète de type 2',
                'description' => 'Résultat d\'une mauvaise gestion de la condition physique et d\'un manque d\'exercice. Diminue la santé et la vitalité du personnage à long terme.',
                'duration' => null,
                'chance' => json_encode([
                    '18-34' => null,
                    '35-59' => 4,
                    '60-79' => 8,
                    '80+' => 10,
                ]),
                'type' => 'negligence',
                'needs_doctor' => true,
                'self_resolving' => false,
                'treatment_cost' => 7500.00,
            ], [
                'name' => 'Obésité ',
                'description' => 'Résulte d\'un manque de soin apporté à la condition physique et d\'un manque d\'activité physique. Réduit la santé, l\'énergie et l\'endurance du personnage.',
                'duration' => null,
                'chance' => json_encode([
                    '18-34' => 2,
                    '35-59' => 4,
                    '60-79' => 8,
                    '80+' => 10,
                ]),
                'type' => 'negligence',
                'needs_doctor' => true,
                'self_resolving' => false,
                'treatment_cost' => 600.00,
            ], [
                'name' => 'Caries dentaires',
                'description' => 'Caries dentaires : Résulte d\'une mauvaise hygiène dentaire et d\'une négligence de la propreté bucco-dentaire. Réduit la santé bucco-dentaire et peut entraîner des infections et des douleurs dentaires.',
                'duration' => null,
                'chance' => json_encode([
                    '18-34' => 10,
                    '35-59' => 20,
                    '60-79' => 25,
                    '80+' => 30,
                ]),
                'type' => 'negligence',
                'needs_doctor' => true,
                'self_resolving' => false,
                'treatment_cost' => 500.00,
            ], [
                'name' => 'Dépression',
                'description' => 'Résulte d\'un niveau élevé de stress, d\'ennui ou d\'un manque de divertissement. Diminue le bonheur et la santé mentale du personnage, affectant sa capacité à interagir avec le monde.
',
                'duration' => null,
                'chance' => null,
                'type' => 'negligence',
                'needs_doctor' => true,
                'self_resolving' => false,
                'treatment_cost' => 800.00,
            ],
            // Maladies graves 
            [
                'name' => 'Cancer ',
                'description' => 'Peut survenir aléatoirement ou comme conséquence d\'une exposition prolongée à des substances toxiques ou d\'une prédisposition génétique. Réduit la santé du personnage et nécessite un traitement intensif pour survivre.
',
                'duration' => null,
                'chance' => json_encode([
                    '18-34' => 1,
                    '35-59' => 2,
                    '60-79' => 5,
                    '80+' => 8,
                ]),
                'type' => 'grave',
                'needs_doctor' => true,
                'self_resolving' => false,
                'treatment_cost' => 2000.00,
            ], [
                'name' => 'Maladie incurable ',
                'description' => 'Très rare et peut survenir en fin de vie si le personnage est négligé pendant une période prolongée. Aucun traitement n\'est disponible, entraînant la mort du personnage.',
                'duration' => null,
                'chance' => json_encode([
                    '18-34' => null,
                    '35-59' => null,
                    '60-79' => 1,
                    '80+' => 2,
                ]),
                'type' => 'grave',
                'needs_doctor' => true,
                'self_resolving' => false,
                'treatment_cost' => null,
            ], [
                'name' => 'Insuffisance rénale',
                'description' => 'Peut survenir en raison d\'une mauvaise hygiène de vie et de l\'abus de certains médicaments. Réduit la santé et la fonction rénale du personnage, nécessitant une dialyse ou une transplantation pour survivre.',
                'duration' => null,
                'chance' => json_encode([
                    '18-34' => 1,
                    '35-59' => 2,
                    '60-79' => 5,
                    '80+' => 15,
                ]),
                'type' => 'grave',
                'needs_doctor' => true,
                'self_resolving' => false,
                'treatment_cost' => 1800.00,
            ],
        ]);
    }
}
