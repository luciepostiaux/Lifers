<?php

namespace Database\Seeders;

use App\Models\InventoriesOutfitBottom;
use Illuminate\Database\Seeder;

class InventoriesOutfitsBottomsSeeder extends Seeder
{
    public function run()
    {
        InventoriesOutfitBottom::create([
            'inventories_id' => 1, // Assurez-vous que cet ID existe dans la table `inventories`
            'outfits_bottoms_id' => 1, // Assurez-vous que cet ID existe dans la table `outfits_bottoms`
            'colors_id' => 1, // Assurez-vous que cet ID existe dans la table `colors`, peut être `null` si `nullable`
        ]);
    }
}
