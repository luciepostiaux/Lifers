<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;
use App\Models\ItemEffect;

class ItemEffectsSeeder extends Seeder
{
    public function run()
    {
        $items = [
            [
                'name' => 'Pomme',
                'effects' => [
                    ['gauge' => 'hunger', 'effect' => 5],
                    ['gauge' => 'thirst', 'effect' => 5],
                ]
            ],
            [
                'name' => 'Pain',
                'effects' => [
                    ['gauge' => 'hunger', 'effect' => 8],
                    ['gauge' => 'thirst', 'effect' => -5],
                ]
            ],
            [
                'name' => 'Riz',
                'effects' => [
                    ['gauge' => 'hunger', 'effect' => 12],
                    ['gauge' => 'thirst', 'effect' => -2],
                ]
            ],
            [
                'name' => 'Poulet grillé',
                'effects' => [
                    ['gauge' => 'hunger', 'effect' => 25],
                    ['gauge' => 'thirst', 'effect' => -4],
                ]
            ],
            [
                'name' => 'Salade',
                'effects' => [
                    ['gauge' => 'hunger', 'effect' => 15],
                ]
            ],
            [
                'name' => 'Pizza',
                'effects' => [
                    ['gauge' => 'hunger', 'effect' => 40],
                    ['gauge' => 'thirst', 'effect' => -10],
                ]
            ],
            [
                'name' => 'Banane',
                'effects' => [
                    ['gauge' => 'hunger', 'effect' => 6],
                    ['gauge' => 'health', 'effect' => 4],
                ]
            ],
            [
                'name' => 'Sushi',
                'effects' => [
                    ['gauge' => 'hunger', 'effect' => 30],
                    ['gauge' => 'thirst', 'effect' => -5],
                ]
            ],
            [
                'name' => 'Chocolat',
                'effects' => [
                    ['gauge' => 'bonheur', 'effect' => 8],
                    ['gauge' => 'hunger', 'effect' => 5],
                ]
            ],
            [
                'name' => 'Glace',
                'effects' => [
                    ['gauge' => 'bonheur', 'effect' => 10],
                    ['gauge' => 'hunger', 'effect' => 10],
                    ['gauge' => 'health', 'effect' => -3],
                ]
            ],
            [
                'name' => 'Gâteau',
                'effects' => [
                    ['gauge' => 'bonheur', 'effect' => 20],
                    ['gauge' => 'hunger', 'effect' => 15],
                    ['gauge' => 'health', 'effect' => -5],
                ]
            ],
            [
                'name' => 'Brocoli',
                'effects' => [
                    ['gauge' => 'health', 'effect' => 18],
                    ['gauge' => 'hunger', 'effect' => 10],
                ]
            ],
            [
                'name' => 'Carottes',
                'effects' => [
                    ['gauge' => 'health', 'effect' => 12],
                    ['gauge' => 'hunger', 'effect' => 8],
                ]
            ],
            [
                'name' => 'Yaourt',
                'effects' => [
                    ['gauge' => 'health', 'effect' => 10],
                    ['gauge' => 'hunger', 'effect' => 12],
                ]
            ],
            [
                'name' => 'Eau',
                'effects' => [
                    ['gauge' => 'thirst', 'effect' => 40],
                ]
            ],
            [
                'name' => 'Soda',
                'effects' => [
                    ['gauge' => 'thirst', 'effect' => 15],
                    ['gauge' => 'health', 'effect' => -5],
                ]
            ],
            [
                'name' => 'Thé glacé',
                'effects' => [
                    ['gauge' => 'thirst', 'effect' => 20],
                ]
            ],
            [
                'name' => 'Jus d\'orange',
                'effects' => [
                    ['gauge' => 'thirst', 'effect' => 30],
                    ['gauge' => 'hunger', 'effect' => 5],
                    ['gauge' => 'health', 'effect' => 5],
                ]
            ],
            [
                'name' => 'Smoothie',
                'effects' => [
                    ['gauge' => 'thirst', 'effect' => 35],
                    ['gauge' => 'hunger', 'effect' => 5],
                    ['gauge' => 'health', 'effect' => 5],
                ]
            ],
            [
                'name' => 'Savon',
                'effects' => [
                    ['gauge' => 'clean', 'effect' => 25],
                ]
            ],
            [
                'name' => 'Shampoing',
                'effects' => [
                    ['gauge' => 'clean', 'effect' => 30],
                ]
            ],
            [
                'name' => 'Dentifrice',
                'effects' => [
                    ['gauge' => 'clean', 'effect' => 20],
                ]
            ],
        ];

        foreach ($items as $itemData) {
            $item = Item::where('name', $itemData['name'])->first();
            if (!$item) {
                continue; // ou créez l'article ici si vous préférez
            }

            foreach ($itemData['effects'] as $effectData) {
                ItemEffect::create([
                    'item_id' => $item->id,
                    'gauge' => $effectData['gauge'],
                    'effect' => $effectData['effect'],
                ]);
            }
        }
    }
}
