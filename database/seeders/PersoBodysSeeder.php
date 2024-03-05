<?php

namespace Database\Seeders;

use App\Models\Perso;
use App\Models\PersoBody;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PersoBodysSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $physiques = [
            ['img_perso' => 'images/perso/perso.png', 'description' => 'Physique 1'],
            ['img_perso' => 'images/perso/perso2.png', 'description' => 'Physique 2'],
            // Ajoutez autant d'entrées que nécessaire
        ];

        foreach ($physiques as $physique) {
            PersoBody::create($physique);
        }
    }
}
