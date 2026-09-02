<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\ItemEffect;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemCatalogSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            foreach ($this->catalog() as $itemData) {
                $aliases = $itemData['aliases'] ?? [];
                $effects = $itemData['effects'] ?? [];
                unset($itemData['aliases'], $itemData['effects']);

                $item = Item::query()->updateOrCreate(
                    ['name' => $itemData['name']],
                    $itemData,
                );

                $this->syncEffects($item, $effects);

                foreach ($aliases as $alias) {
                    $this->mergeLegacyItem($alias, $item);
                }
            }
        });
    }

    private function catalog(): array
    {
        return [
            $this->item('Pomme', 5, Item::CATEGORY_FOOD, 'Une pomme fraîche, légère et naturellement riche en fibres.', 'foods/pomme.png', [
                'hunger' => 8,
                'thirst' => 2,
                'health' => 2,
            ], ['Pomme — démo']),
            $this->item('Pain', 8, Item::CATEGORY_FOOD, 'Du pain frais pour calmer efficacement une petite faim.', 'foods/pain.png', [
                'hunger' => 14,
                'thirst' => -2,
            ]),
            $this->item('Riz', 12, Item::CATEGORY_FOOD, 'Un bol de riz rassasiant, simple et nourrissant.', 'foods/riz.png', [
                'hunger' => 22,
                'thirst' => -3,
            ]),
            $this->item('Poulet rôti', 32, Item::CATEGORY_FOOD, 'Un poulet rôti généreux pour un repas complet.', 'foods/poulet-roti.png', [
                'hunger' => 45,
                'thirst' => -5,
                'health' => 5,
            ], ['Poulet grillé']),
            $this->item('Salade', 16, Item::CATEGORY_FOOD, 'Un mélange frais et équilibré de légumes croquants.', 'foods/salade.png', [
                'hunger' => 18,
                'thirst' => 3,
                'health' => 6,
            ]),
            $this->item('Pizza', 30, Item::CATEGORY_FOOD, 'Une pizza généreuse qui rassasie et fait plaisir.', 'foods/pizza.png', [
                'hunger' => 50,
                'thirst' => -8,
                'happiness' => 8,
                'health' => -5,
            ]),
            $this->item('Banane', 6, Item::CATEGORY_FOOD, 'Une banane mûre, pratique pour reprendre un peu d’énergie.', 'foods/banane.png', [
                'hunger' => 10,
                'health' => 3,
            ]),
            $this->item('Sushis', 32, Item::CATEGORY_FOOD, 'Un assortiment de sushis frais au poisson et au riz vinaigré.', 'foods/sushis.png', [
                'hunger' => 38,
                'thirst' => -3,
                'health' => 4,
            ], ['Sushi']),
            $this->item('Chocolat', 12, Item::CATEGORY_FOOD, 'Quelques carrés de chocolat pour une pause réconfortante.', 'foods/chocolat.png', [
                'hunger' => 6,
                'happiness' => 10,
                'health' => -2,
            ]),
            $this->item('Glace', 14, Item::CATEGORY_FOOD, 'Une glace crémeuse pour se rafraîchir et se faire plaisir.', 'foods/glace.png', [
                'hunger' => 8,
                'thirst' => 3,
                'happiness' => 12,
                'health' => -3,
            ]),
            $this->item('Gâteau', 24, Item::CATEGORY_FOOD, 'Une part de gâteau moelleux pour les grandes envies sucrées.', 'foods/gateau.png', [
                'hunger' => 20,
                'thirst' => -4,
                'happiness' => 18,
                'health' => -5,
            ]),
            $this->item('Mélange de légumes', 18, Item::CATEGORY_FOOD, 'Un mélange coloré de légumes pour un repas sain et équilibré.', 'foods/mix-legumes.png', [
                'hunger' => 20,
                'thirst' => 3,
                'health' => 10,
            ], ['Brocoli', 'Carottes']),
            $this->item('Bol de yaourt', 10, Item::CATEGORY_FOOD, 'Un bol de yaourt onctueux, doux et facile à digérer.', 'foods/yaourt.png', [
                'hunger' => 15,
                'thirst' => 2,
                'health' => 4,
            ], ['Yaourt']),
            $this->item('Soupe', 18, Item::CATEGORY_FOOD, 'Une soupe chaude qui nourrit tout en aidant à s’hydrater.', 'foods/soupe.png', [
                'hunger' => 18,
                'thirst' => 12,
                'health' => 4,
            ]),

            $this->item('Eau', 5, Item::CATEGORY_DRINK, 'Une bouteille d’eau fraîche pour étancher efficacement la soif.', 'foods/eau.png', [
                'thirst' => 30,
            ], ['Bouteille d’eau — démo']),
            $this->item('Soda', 8, Item::CATEGORY_DRINK, 'Une boisson gazeuse sucrée, rafraîchissante mais peu saine.', 'foods/soda.png', [
                'thirst' => 18,
                'happiness' => 4,
                'health' => -3,
            ]),
            $this->item('Thé glacé', 10, Item::CATEGORY_DRINK, 'Un thé glacé léger et rafraîchissant.', 'foods/the-glace.png', [
                'thirst' => 24,
                'happiness' => 2,
            ]),
            $this->item('Smoothie', 18, Item::CATEGORY_DRINK, 'Un smoothie fruité qui hydrate et apporte quelques vitamines.', 'foods/smoothie.png', [
                'thirst' => 22,
                'hunger' => 8,
                'health' => 6,
            ], ['Jus d’orange', "Jus d'orange"]),

            $this->item('Savon', 8, Item::CATEGORY_HYGIENE_AND_WELLBEING, 'Un savon parfumé pour garder une peau propre et fraîche.', 'hygiene/savon.png', [
                'clean' => 20,
            ], ['Savon — démo']),
            $this->item('Shampoing', 12, Item::CATEGORY_HYGIENE_AND_WELLBEING, 'Un shampoing nourrissant pour prendre soin de ses cheveux.', 'hygiene/shampoing.png', [
                'clean' => 25,
                'happiness' => 2,
            ]),
            $this->item('Dentifrice', 7, Item::CATEGORY_HYGIENE_AND_WELLBEING, 'Un dentifrice frais pour compléter la routine d’hygiène.', 'hygiene/dentifrice.png', [
                'clean' => 15,
                'health' => 2,
            ], [], 1, 'oral_care'),
            $this->item('Crème pour le corps', 15, Item::CATEGORY_HYGIENE_AND_WELLBEING, 'Une crème douce pour prendre soin de sa peau et de son bien-être.', 'hygiene/creme-corps.png', [
                'clean' => 8,
                'happiness' => 12,
            ]),
            $this->item(Item::FAMILY_PROTECTION_NAME, 10, Item::CATEGORY_HYGIENE_AND_WELLBEING, 'Une boîte de 20 préservatifs pour les moments protégés.', 'hygiene/preservatif.png', [], [], 20),

            $this->item('Cigarette', 8, Item::CATEGORY_TOBACCO_AND_ALCOHOL, 'Une cigarette qui détend brièvement, mais dégrade la santé et la propreté.', 'foods/cigarette.png', [
                'happiness' => 3,
                'clean' => -3,
                'health' => -5,
            ], [], 1, 'tobacco'),
            $this->item('Whisky', 18, Item::CATEGORY_TOBACCO_AND_ALCOHOL, 'Un verre de whisky qui divertit sur le moment, au prix d’une déshydratation et d’un effet nocif sur la santé.', 'foods/whiskey.png', [
                'thirst' => -8,
                'happiness' => 8,
                'entertainment' => 4,
                'health' => -6,
            ], [], 1, 'alcohol'),
        ];
    }

    private function item(
        string $name,
        int $price,
        string $category,
        string $description,
        string $image,
        array $effects,
        array $aliases = [],
        int $unitsPerPurchase = 1,
        ?string $usageTag = null,
    ): array {
        return [
            'name' => $name,
            'price' => $price,
            'units_per_purchase' => $unitsPerPurchase,
            'description' => $description,
            'image_path' => "/images/items/{$image}",
            'background_image_path' => null,
            'category' => $category,
            'usage_tag' => $usageTag,
            'effects' => $effects,
            'aliases' => $aliases,
        ];
    }

    private function syncEffects(Item $item, array $effects): void
    {
        ItemEffect::query()
            ->where('item_id', $item->id)
            ->when(
                $effects !== [],
                fn ($query) => $query->whereNotIn('gauge', array_keys($effects)),
            )
            ->delete();

        foreach ($effects as $gauge => $effect) {
            ItemEffect::query()->updateOrCreate(
                ['item_id' => $item->id, 'gauge' => $gauge],
                ['effect' => $effect],
            );
        }
    }

    private function mergeLegacyItem(string $legacyName, Item $item): void
    {
        $legacyItem = Item::query()
            ->where('name', $legacyName)
            ->whereKeyNot($item->id)
            ->first();

        if (! $legacyItem) {
            return;
        }

        DB::table('inventory_items')
            ->where('item_id', $legacyItem->id)
            ->get()
            ->each(function ($legacyInventoryItem) use ($item) {
                $existingQuantity = DB::table('inventory_items')
                    ->where('inventory_id', $legacyInventoryItem->inventory_id)
                    ->where('item_id', $item->id)
                    ->value('quantity') ?? 0;

                DB::table('inventory_items')->updateOrInsert(
                    [
                        'inventory_id' => $legacyInventoryItem->inventory_id,
                        'item_id' => $item->id,
                    ],
                    [
                        'quantity' => $existingQuantity + $legacyInventoryItem->quantity,
                        'created_at' => $legacyInventoryItem->created_at ?? now(),
                        'updated_at' => now(),
                    ],
                );
            });

        $legacyItem->delete();
    }
}
