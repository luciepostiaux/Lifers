<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Perso;
use App\Models\Sickness;

class PersoSicknessSeeder extends Seeder
{
    public function run()
    {
        // Exemple: Attribuer la Grippe (id=1) au premier personnage (id=1) avec des effets sur les jauges
        $perso = Perso::find(1); // Assurez-vous que ce Perso existe
        $sickness = Sickness::find(1); // Assurez-vous que cette Sickness existe

        // Si vous avez déjà des relations entre Perso et Sickness
        if ($perso && $sickness) {
            $perso->sicknesses()->attach($sickness->id);
            // Si vous voulez inclure des effets sur les jauges, ajoutez-les ici
            // Cela nécessiterait d'ajuster votre modèle ou la structure de la base de données pour stocker ces informations
        }
    }
}
