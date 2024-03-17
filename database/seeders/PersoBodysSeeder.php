<?php

namespace Database\Seeders;

use App\Models\PersoBody;
use Illuminate\Database\Seeder;
use App\Models\Gender;

class PersoBodysSeeder extends Seeder
{
    public function run()
    {
        // Suppose que vous avez des genres 'Physique 1', 'Physique 2', etc.
        $physique1 = Gender::where('name', 'Physique 1')->first();
        $physique2 = Gender::where('name', 'Physique 2')->first();

        $physiques = [
            ['img_perso' => 'images/perso/perso.png', 'description' => 'Physique 1', 'genders_id' => $physique1->id],
            ['img_perso' => 'images/perso/perso2.png', 'description' => 'Physique 2', 'genders_id' => $physique2->id],
        ];

        foreach ($physiques as $physique) {
            PersoBody::create($physique);
        }
    }
}
