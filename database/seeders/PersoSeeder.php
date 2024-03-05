<?php


namespace Database\Seeders;

use App\Models\Perso;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PersoSeeder extends Seeder
{
    public function run()
    {
        // Insérer un personnage spécifique
        Perso::create([
            'first_name' => 'Ludan',
            'last_name' => 'Dells',
            'birth_date' => now(),
            'money' => 100000.00,
            'user_id' => 1,
            'hairstyles_id' => 1,
            'perso_outfits_id' => null,
            'jobs_id' => 1,
            'salary' => null,
            'mother_id' => null,
            'father_id' => null,
            'partner_id' => null,
            'genders_id' => 1,
            'perso_bodies_id' => 1,
        ]);

        // Perso::create([
        //     'first_name' => 'Lyanna',
        //     'last_name' => 'Dells',
        //     'birth_date' => now(),
        //     'money' => 100000.00,
        //     'user_id' => 2,
        //     'hairstyles_id' => 1,
        //     'perso_outfits_id' => null,
        //     'jobs_id' => 1,
        //     'salary' => null,
        //     'studies_id' => 2,
        //     'mother_id' => null,
        //     'father_id' => null,
        //     'partner_id' => null,
        //     'genders_id' => 1,
        //     'perso_bodies_id' => 2,

        // ]);


        Perso::factory()->count(25)->create();
    }
}
