<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Perso;
use App\Models\Diploma;

class PersoDiplomasSeeder extends Seeder
{
    public function run()
    {
        // Supposons que vous souhaitiez assigner des diplômes de manière aléatoire aux personnages
        $persos = Perso::all();
        $diplomas = Diploma::all()->pluck('id');

        foreach ($persos as $perso) {
            // Assigner entre 1 et 3 diplômes aléatoires à chaque perso
            $assignedDiplomas = $diplomas->random(rand(1, 3));
            $perso->diplomas()->attach($assignedDiplomas);
        }
    }
}
