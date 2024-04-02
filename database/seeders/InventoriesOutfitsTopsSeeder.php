<?php

namespace Database\Seeders;

use App\Models\InventoriesOutfitTop;
use Illuminate\Database\Seeder;

class InventoriesOutfitsTopsSeeder extends Seeder
{
    public function run()
    {
        InventoriesOutfitTop::create([
            'inventories_id' => 1, // Assurez-vous que cet ID existe dans la table `inventories`
            'outfits_tops_id' => 1, // Assurez-vous que cet ID existe dans la table `outfits_tops`
            'colors_id' => 1, // Assurez-vous que cet ID existe dans la table `colors`, peut être `null` si `nullable`
        ]);
    }
}
