<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResidencesTableSeeder extends Seeder
{
    public function run()
    {
        $residences = [
            [
                'type' => 'Caravane rouillée',
                'prix_achat' => 350,
                'prix_mensuel' => null,
                'taxe_mensuelle' => null,
                'nombre_paiements' => 1,
                'description' => "Une caravane rouillée et abandonnée, perchée sur des roues dégonflées. Les morceaux de tôle manquent par endroits, exposant l'intérieur à la rudesse du temps. Les fenêtres sont fissurées, laissant filtrer la lumière terne à travers les volets délabrés.",
                'image_path' =>
                'images/residences/caravane-1.webp',
            ],
            [
                'type' => 'Caravane précaire',
                'prix_achat' => 400,
                'prix_mensuel' => null,
                'taxe_mensuelle' => null,
                'nombre_paiements' => 1,
                'description' => "À l'intérieur, l'espace est exigu et sombre. Les meubles sont des vestiges d'un passé révolu, usés par les années et imprégnés de l'odeur de l'humidité. Un vieux matelas éventré occupe un coin, tandis qu'une table branlante sert de support pour une bougie vacillante.",
                'image_path' =>
                'images/residences/caravane-2.webp',
            ],
            [
                'type' => 'Maison rudimentaire',
                'prix_achat' => 900,
                'prix_mensuel' => 300,
                'taxe_mensuelle' => 100,
                'nombre_paiements' => 3,
                'description' => "La cuisine est équipée de manière rudimentaire, avec une cuisinière à gaz datant d'une autre époque et un évier ébréché. Le chauffage est assuré par un vieux poêle à bois, tandis que l'électricité est fournie par un réseau électrique vieillissant et capricieux.",
                'image_path' => 'images/residences/middle-1.webp',

            ],
            [
                'type' => 'Maison dépouillée',
                'prix_achat' => 950,
                'prix_mensuel' => 350,
                'taxe_mensuelle' => 100,
                'nombre_paiements' => 3,
                'description' => "À l'intérieur, l'ameublement est simple et utilitaire. Des chaises dépareillées entourent une table bancale dans la salle à manger, tandis qu'un vieux canapé élimé occupe une place centrale dans le salon. Les murs nus révèlent des traces de moisissures et d'humidité.",
                'image_path' => 'images/residences/middle-2.webp',

            ],
            [
                'type' => 'Maison fonctionnelle',
                'prix_achat' => 1400,
                'prix_mensuel' => 350,
                'taxe_mensuelle' => 150,
                'nombre_paiements' => 3,
                'description' => "Une maisonnette typique de banlieue, avec un extérieur propre et bien entretenu. La pelouse est tondue régulièrement et quelques fleurs colorées ornent l'entrée. Les fenêtres sont propres et la peinture fraîche.",
                'image_path' => 'images/residences/middle-3.webp',

            ],
            [
                'type' => 'Maison spacieuce',
                'prix_achat' => 2200,
                'prix_mensuel' => 500,
                'taxe_mensuelle' => 180,
                'nombre_paiements' => 3,
                'description' => "Une villa suburbaine imposante, avec un extérieur élégant et soigné. Les jardins bien entretenus entourent la propriété, avec des massifs de fleurs colorées et des arbres majestueux. Une allée pavée mène à l'entrée principale, où un porche accueillant invite les visiteurs à entrer.",
                'image_path' => 'images/residences/home-1.webp',

            ],
            [
                'type' => 'Maison bien équipée',
                'prix_achat' => 2500,
                'prix_mensuel' => 600,
                'taxe_mensuelle' => 200,
                'nombre_paiements' => 3,
                'description' => "La cuisine est équipée des derniers appareils électroménagers, tels qu'un four à induction et une machine à café intégrée. La climatisation assure un confort optimal, tandis que les systèmes domotiques permettent de contrôler l'éclairage, le chauffage et la sécurité à distance.",
                'image_path' => 'images/residences/home-2.webp',

            ],
            [
                'type' => 'Château Majestueux',
                'prix_achat' => 80000,
                'prix_mensuel' => null,
                'taxe_mensuelle' => null,
                'nombre_paiements' => 1,
                'description' => "À l'intérieur, le luxe est omniprésent. Des lustres en cristal étincellent dans les salons somptueux, où des tapisseries anciennes ornent les murs et des meubles d'époque ajoutent une touche de grandeur. Les cheminées en marbre crépitent doucement, réchauffant l'atmosphère déjà chaleureuse.",
                'image_path' => 'images/residences/castle-1.webp',

            ],
        ];

        foreach ($residences as $residence) {
            DB::table('residences')->insert($residence);
        }
    }
}
