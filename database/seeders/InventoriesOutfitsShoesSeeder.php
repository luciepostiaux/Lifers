<?php

namespace Database\Seeders;

use App\Models\InventoriesOutfitShoe;
use Illuminate\Database\Seeder;

class InventoriesOutfitsShoesSeeder extends Seeder
{
    public function run()
    {
        InventoriesOutfitShoe::create([
            'inventories_id' => 1, // Assurez-vous que cet ID existe dans la table `inventories`
            'outfits_shoes_id' => 1, // Assurez-vous que cet ID existe dans la table `outfits_shoes`
            'colors_id' => 1, // Assurez-vous que cet ID existe dans la table `colors`, peut être `null` si `nullable`
        ]);
    }
}
