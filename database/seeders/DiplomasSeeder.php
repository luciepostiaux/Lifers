<?php

namespace Database\Seeders;

use App\Models\Diploma;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DiplomasSeeder extends Seeder
{
    public function run(): void
    {
        Diploma::insert([
            ['name' => 'Année Générale en Sciences de la Santé', 'description' => 'Félicitations pour l\'obtention de ton diplôme en Année Générale en Sciences de la Santé ! Tu as désormais une base solide dans les principes fondamentaux de la santé humaine, te préparant à poursuivre des études spécialisées ou à entrer dans le domaine de la santé avec une compréhension approfondie.'],
            ['name' => 'Spécialisation en Médecine Générale', 'description' => 'Bravo pour avoir obtenu ta spécialisation en Médecine Générale ! Tu es maintenant un médecin généraliste compétent, prêt à fournir des soins complets et holistiques à tes patients, tout en étant leur premier point de contact pour les problèmes de santé.'],
            ['name' => 'Spécialisation en Chirurgie', 'description' => "Félicitations pour ta spécialisation en Chirurgie ! Tu as acquis les compétences techniques et la précision nécessaires pour pratiquer la chirurgie avec succès, sauvant des vies et améliorant la qualité de vie des patients grâce à ton expertise."],
            ['name' => 'Spécialisation en Psychiatrie', 'description' => "Bravo pour avoir terminé ta spécialisation en Psychiatrie ! Tu es maintenant un expert dans l'évaluation, le diagnostic et le traitement des troubles mentaux, offrant un soutien crucial à ceux qui en ont besoin et contribuant à l'amélioration de leur bien-être mental."],
            ['name' => 'Spécialisation en Soins Infirmiers', 'description' => "Félicitations pour ton diplôme en Spécialisation en Soins Infirmiers ! En tant qu'infirmier qualifié, tu es le pilier du système de santé, fournissant des soins attentionnés et compétents à tes patients tout en apportant confort et soutien à leurs familles."],
            ['name' => 'Spécialisation en Pharmacologie', 'description' => 'Bravo pour avoir complété ta spécialisation en Pharmacologie ! Tu es maintenant un expert dans l\'étude des médicaments et de leur impact sur le corps humain, contribuant à l\'avancement de la science médicale et à l\'amélioration des traitements disponibles pour les patients.'],
            //techno
            ['name' => 'Année Générale Ingénierie et Technologie', 'description' => "Félicitations pour l'obtention de ton diplôme en Année Générale en Ingénierie et Technologie ! Tu as maintenant les compétences de base nécessaires pour aborder les défis complexes du monde de l'ingénierie et de la technologie, prêt à les résoudre avec créativité et logique."],

            ['name' => 'Spécialisation en Génie Civil', 'description' => "Bravo pour ta spécialisation en Génie Civil ! En tant qu'expert, tu es désormais capable de concevoir et de superviser la construction de structures essentielles telles que des bâtiments, des routes et des ponts, contribuant ainsi à façonner le monde physique qui nous entoure."],

            ['name' => 'Spécialisation en Génie Mécanique', 'description' => "Félicitations pour avoir obtenu ta spécialisation en Génie Mécanique ! Tu as maintenant les compétences nécessaires pour concevoir, analyser et améliorer une variété de systèmes mécaniques, contribuant ainsi à rendre notre vie quotidienne plus efficace et plus confortable."],

            ['name' => 'Spécialisation en Génie Électrique', 'description' => "Bravo pour ta spécialisation en Génie Électrique ! En tant qu'ingénieur électricien, tu es maintenant capable de concevoir, développer et superviser des systèmes électriques complexes, participant ainsi à l'avancement des technologies qui alimentent notre monde moderne."],

            ['name' => 'Spécialisation en Informatique', 'description' => "Félicitations pour ta spécialisation en Informatique ! En tant qu'expert en informatique, tu as le pouvoir de créer des solutions informatiques innovantes qui transforment notre façon de vivre, de travailler et de communiquer dans l'ère numérique."],

            ['name' => 'Spécialisation en Aérospatiale', 'description' => "Bravo pour avoir terminé ta spécialisation en Aérospatiale ! Tu es maintenant un expert dans la conception et la fabrication de véhicules aériens et spatiaux, contribuant ainsi à l'exploration et à la compréhension des frontières de notre univers."],
            //Social
            ['name' => 'Année Générale en Sciences Humaines et Sociales', 'description' => "Félicitations pour l'obtention de ton diplôme en Année Générale en Sciences Humaines et Sociales ! Tu as désormais une compréhension approfondie de la société humaine et de ses dynamiques, te préparant à influencer positivement le tissu social qui nous entoure."],
            ['name' => 'Spécialisation en Psychologie', 'description' => "Plonge dans les profondeurs de l'esprit humain, décryptant les mystères de nos pensées, émotions et comportements. En psychologie, tu deviens le guide vers le bien-être et la compréhension de soi, changeant des vies une âme à la fois."],
            ['name' => 'Spécialisation en Sociologie', 'description' => "Observe, analyse et comprends les dynamiques de nos sociétés. La sociologie te donne les clés pour déchiffrer les structures sociales, les tendances et les transformations, te permettant d'apporter des solutions éclairées aux défis de nos communautés."],
            ['name' => 'Spécialisation en Anthropologie', 'description' => "Bravo pour avoir obtenu ta spécialisation en Anthropologie ! En tant qu'anthropologue, tu es désormais un expert dans l'étude des cultures et des sociétés humaines, contribuant ainsi à une meilleure compréhension de la diversité et de la richesse de l'humanité."],
            ['name' => 'Spécialisation en Droit', 'description' => "Deviens le gardien de la justice, maniant le droit comme un outil au service de l'équité et de l'ordre. La spécialisation en droit te positionne au cœur des systèmes qui régissent nos vies, te donnant le pouvoir de défendre, de conseiller et de guider."],
            ['name' => 'Spécialisation en Économie', 'description' => "Bravo pour ta spécialisation en Économie ! En tant qu'économiste, tu es désormais un expert dans l'analyse des forces économiques et des politiques publiques, contribuant ainsi à la prospérité et au bien-être de la société."],
            // ['name' => '', 'description' => ''],
            // ['name' => '', 'description' => ''],
            // ['name' => '', 'description' => ''],
            // ['name' => '', 'description' => ''],
            // ['name' => '', 'description' => ''],
            // ['name' => '', 'description' => ''],
            // ['name' => '', 'description' => ''],
            // ['name' => '', 'description' => ''],

        ]);
    }
}
