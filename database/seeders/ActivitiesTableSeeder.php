<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activity;

class ActivitiesTableSeeder extends Seeder
{
    public function run()
    {
        // Spa activities
        Activity::create([
            'name' => 'Formule de base',
            'description' => 'Accès aux installations de base (sauna, hammam)',
            'price' => 65,
            'category' => 'Spa',
        ]);

        Activity::create([
            'name' => 'Formule intermédiaire',
            'description' => 'Accès aux installations de base + massage relaxant',
            'price' => 85.50,
            'category' => 'Spa',
        ]);

        Activity::create([
            'name' => 'Formule premium',
            'description' => 'Accès complet aux installations + massage VIP',
            'price' => 125,
            'category' => 'Spa',
        ]);

        // Parc d'attractions activities
        Activity::create([
            'name' => 'Attractions douces',
            'description' => 'Manèges pour enfants, promenades',
            'price' => 85,
            'category' => 'Parc d\'attractions',
        ]);

        Activity::create([
            'name' => 'Attractions à sensations fortes',
            'description' => 'Montagnes russes, tours de chute',
            'price' => 100,
            'category' => 'Parc d\'attractions',
        ]);

        Activity::create([
            'name' => 'Spectacles et divertissements',
            'description' => 'Spectacles de magie, concerts en plein air',
            'price' => 145,
            'category' => 'Parc d\'attractions',
        ]);

        // Zoo activities
        Activity::create([
            'name' => 'Entrée de base',
            'description' => 'Visite des enclos et des expositions principales',
            'price' => 55,
            'category' => 'Zoo',
        ]);

        Activity::create([
            'name' => 'Visites guidées',
            'description' => 'Visite avec un guide pour en apprendre davantage sur les animaux',
            'price' => 75.50,
            'category' => 'Zoo',
        ]);

        Activity::create([
            'name' => 'Expériences interactives',
            'description' => 'Possibilité de nourrir certains animaux, rencontrer les gardiens',
            'price' => 180,
            'category' => 'Zoo',
        ]);

        // Cinema activities
        Activity::create([
            'name' => 'Projection de films',
            'description' => 'Projection de films récents et classiques dans différentes salles',
            'price' => 45,
            'category' => 'Cinéma',
        ]);

        Activity::create([
            'name' => 'Séances spéciales',
            'description' => 'Billets pour des avant-premières, marathons de films, soirées à thème',
            'price' => 60.50,
            'category' => 'Cinéma',
        ]);

        Activity::create([
            'name' => 'Offres de snacks et de boissons',
            'description' => 'Pour une expérience cinématographique complète',
            'price' => 80,
            'category' => 'Cinéma',
        ]);
    }
}
