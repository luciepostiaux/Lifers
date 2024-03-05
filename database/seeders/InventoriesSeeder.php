<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Inventory;
use App\Models\Perso;

class InventoriesSeeder extends Seeder
{
    public function run()
    {
        // Récupère tous les personnages
        $personnages = Perso::all();

        // Crée un inventaire pour chaque personnage
        foreach ($personnages as $perso) {
            Inventory::create([
                'perso_id' => $perso->id,
            ]);
        }
    }
}
