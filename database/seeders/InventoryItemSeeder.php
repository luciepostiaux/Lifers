<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Inventory;
use App\Models\Item;

class InventoryItemSeeder extends Seeder
{
    public function run()
    {
        // // Supposons que vous voulez lier les items à l'inventaire du premier perso
        // $inventory = Inventory::where('perso_id', 1)->first();

        // // Assurez-vous que l'inventaire existe
        // if ($inventory) {
        //     // Obtenir les ID des items que vous souhaitez ajouter à l'inventaire
        //     $itemIds = Item::whereIn('name', ['Pomme', /* Autres noms d'items ici */])->pluck('id');

        //     // Associer les items à l'inventaire
        //     $inventory->items()->attach($itemIds);
        // }
    }
}
