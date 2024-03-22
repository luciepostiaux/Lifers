<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Inventory;
use App\Models\Perso;

class InventoriesSeeder extends Seeder
{
    public function run()
    {
        // Assurez-vous que les personnages existent
        $persos = Perso::all();

        foreach ($persos as $perso) {
            Inventory::create([
                'perso_id' => $perso->id,
            ]);
        }
    }
}
