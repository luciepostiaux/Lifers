<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Gender;
use App\Models\PersoBody;

class PersoBodysSeeder extends Seeder
{
    public function run()
    {
        $femme = Gender::where('name', 'Physique 1')->first();
        $homme = Gender::where('name', 'Physique 2')->first();

        $femmesBodies = [
            ['img_perso' => 'images/perso/A-1.webp', 'description' => 'Description de femme physique 1'],
            ['img_perso' => 'images/perso/A-2.webp', 'description' => 'Description de femme physique 2'],
            ['img_perso' => 'images/perso/A-3.webp', 'description' => 'Description de femme physique 3'],
            ['img_perso' => 'images/perso/A-4.webp', 'description' => 'Description de femme physique 4'],
        ];

        $hommesBodies = [
            ['img_perso' => 'images/perso/B-1.webp', 'description' => 'Description de homme physique 1'],
            ['img_perso' => 'images/perso/B-2.webp', 'description' => 'Description de homme physique 2'],
            ['img_perso' => 'images/perso/B-3.webp', 'description' => 'Description de homme physique 3'],
            ['img_perso' => 'images/perso/B-4.webp', 'description' => 'Description de homme physique 4'],
        ];

        foreach ($femmesBodies as $body) {
            PersoBody::create([
                'img_perso' => $body['img_perso'],
                'description' => $body['description'],
                'genders_id' => $femme->id,
            ]);
        }

        foreach ($hommesBodies as $body) {
            PersoBody::create([
                'img_perso' => $body['img_perso'],
                'description' => $body['description'],
                'genders_id' => $homme->id,
            ]);
        }
    }
}
