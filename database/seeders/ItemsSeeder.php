<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;

class ItemsSeeder extends Seeder
{
    public function run()
    {
        Item::create([
            'name' => 'Pomme',
            'price' => 5,
            'description' => 'Une délicieuse pomme fraîche, riche en fibres.',
            'img_item' => 'images/items/foods/apple.png',
            'category' => 'Nourriture',
        ]);

        Item::create([
            'name' => 'Pain',
            'price' => 8.75,
            'description' => 'Un morceau de pain frais, idéal pour calmer votre faim.',
            'img_item' => 'images/items/foods/apple.png',
            'category' => 'Nourriture',
        ]);

        Item::create([
            'name' => 'Riz',
            'price' => 14,
            'description' => 'Des grains de riz savoureux, parfaits pour un repas copieux.',
            'img_item' => 'images/items/foods/apple.png',
            'category' => 'Nourriture',
        ]);

        Item::create([
            'name' => 'Poulet grillé',
            'price' => 55,
            'description' => 'Un morceau de poulet grillé tendre et juteux.',
            'img_item' => 'images/items/foods/apple.png',
            'category' => 'Nourriture',
        ]);

        Item::create([
            'name' => 'Salade',
            'price' => 27.50,
            'description' => 'Un mélange de légumes frais, parfait pour une alimentation saine.',
            'img_item' => 'images/items/foods/apple.png',
            'category' => 'Nourriture',
        ]);

        Item::create([
            'name' => 'Pizza',
            'price' => 30,
            'description' => 'Une délicieuse pizza garnie de fromage et de pepperoni.',
            'img_item' => 'images/items/foods/apple.png',
            'category' => 'Nourriture',
        ]);

        Item::create([
            'name' => 'Banane',
            'price' => 6,
            'description' => 'Une banane mûre, pleine de potassium pour vous donner de l\'énergie.',
            'img_item' => 'images/items/foods/apple.png',
            'category' => 'Nourriture',
        ]);

        Item::create([
            'name' => 'Sushi',
            'price' => 45,
            'description' => 'Des sushis frais préparés avec du poisson cru et du riz vinaigré.',
            'img_item' => 'images/items/foods/apple.png',
            'category' => 'Nourriture',
        ]);

        Item::create([
            'name' => 'Chocolat',
            'price' => 38.50,
            'description' => 'Des morceaux de chocolat fondant qui vous mettront de bonne humeur.',
            'img_item' => 'images/items/foods/apple.png',
            'category' => 'Nourriture',
        ]);

        Item::create([
            'name' => 'Glace',
            'price' => 44,
            'description' => 'Une délicieuse glace crémeuse, parfaite pour se remonter le moral.',
            'img_item' => 'images/items/foods/apple.png',
            'category' => 'Nourriture',
        ]);

        Item::create([
            'name' => 'Gâteau',
            'price' => 55,
            'description' => 'Un délicieux gâteau moelleux, idéal pour les occasions spéciales.',
            'img_item' => 'images/items/foods/apple.png',
            'category' => 'Nourriture',
        ]);

        Item::create([
            'name' => 'Brocoli',
            'price' => 22,
            'description' => 'Des bouquets de brocoli frais, riches en vitamines et en fibres.',
            'img_item' => 'images/items/foods/apple.png',
            'category' => 'Nourriture',
        ]);

        Item::create([
            'name' => 'Carottes',
            'price' => 16.50,
            'description' => 'Des carottes croquantes, pleines de bêta-carotène pour une bonne santé oculaire.',
            'img_item' => 'images/items/foods/apple.png',
            'category' => 'Nourriture',
        ]);

        Item::create([
            'name' => 'Yaourt',
            'price' => 15.50,
            'description' => 'Un yaourt crémeux et probiotique, bon pour la digestion.',
            'img_item' => 'images/items/foods/apple.png',
            'category' => 'Nourriture',
        ]);

        Item::create([
            'name' => 'Eau',
            'price' => 45,
            'description' => 'Une bouteille d\'eau fraîche et hydratante.',
            'img_item' => 'images/items/foods/apple.png',
            'category' => 'Boisson',
        ]);

        Item::create([
            'name' => 'Soda',
            'price' => 18,
            'description' => 'Une boisson gazeuse sucrée et rafraîchissante.',
            'img_item' => 'images/items/foods/apple.png',
            'category' => 'Boisson',
        ]);

        Item::create([
            'name' => 'Thé glacé',
            'price' => 25.50,
            'description' => 'Une boisson rafraîchissante à base de thé glacé et de citron.',
            'img_item' => 'images/items/foods/apple.png',
            'category' => 'Boisson',
        ]);

        Item::create([
            'name' => 'Jus d\'orange',
            'price' => 35,
            'description' => 'Un jus d\'orange frais pressé, plein de vitamine C.',
            'img_item' => 'images/items/foods/apple.png',
            'category' => 'Boisson',
        ]);

        Item::create([
            'name' => 'Smoothie',
            'price' => 48.50,
            'description' => 'Un smoothie frais et fruité, parfait pour se désaltérer.',
            'img_item' => 'images/items/foods/apple.png',
            'category' => 'Boisson',
        ]);

        Item::create([
            'name' => 'Savon',
            'price' => 30,
            'description' => 'Un savon parfumé pour vous garder propre et frais.',
            'img_item' => 'images/items/foods/apple.png',
            'category' => 'Propreté',
        ]);

        Item::create([
            'name' => 'Shampoing',
            'price' => 38.50,
            'description' => 'Un shampoing nourrissant pour des cheveux brillants et sains.',
            'img_item' => 'images/items/foods/apple.png',
            'category' => 'Propreté',
        ]);

        Item::create([
            'name' => 'Dentifrice',
            'price' => 22.50,
            'description' => 'Un dentifrice rafraîchissant pour une haleine fraîche toute la journée.',
            'img_item' => 'images/items/foods/apple.png',
            'category' => 'Propreté',
        ]);
        // Item::factory()->count(10)->create();
    }
}
